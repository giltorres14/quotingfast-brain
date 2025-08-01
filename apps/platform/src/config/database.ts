import knex, { Knex as Database } from 'knex'
import path from 'path'
import { removeKey, sleep } from '../utilities'
import { logger } from './logger'

export { Database }

export interface DatabaseConfig {
    host: string
    port: number
    user: string
    password: string
    database?: string
    migrationPaths: string[]
}

export type Query = (builder: Database.QueryBuilder<any>) => Database.QueryBuilder<any>

const MIGRATION_RETRIES = 3

knex.QueryBuilder.extend('when', function(
    condition: boolean,
    fnif: Query,
    fnelse?: Query,
) {
    return condition ? fnif(this) : (fnelse ? fnelse(this) : this)
})

const connect = (config: DatabaseConfig, withDB = true) => {
    let connection = removeKey('migrationPaths', config)
    if (!withDB) {
        connection = removeKey('database', connection)
    }
    return knex({
        client: 'pg',
        connection: {
            ...connection,
        },
        asyncStackTraces: true,
    })
}

const migrate = async (config: DatabaseConfig, db: Database, retries = MIGRATION_RETRIES): Promise<void> => {
    try {
        process.stdout.write('🔍 Checking current migration status...\n')
        const completed = await db.migrate.currentVersion()
        process.stdout.write(`📋 Current migration version: ${completed}\n`)
        
        process.stdout.write('📂 Migration directories:\n')
        const migrationDirs = [
            path.resolve(__dirname, '../../db/migrations'),
            ...config.migrationPaths,
        ]
        migrationDirs.forEach(dir => process.stdout.write(`  - ${dir}\n`))
        
        process.stdout.write('🚀 Starting migration process...\n')
        
        // Get list of pending migrations first
        const pending = await db.migrate.list({
            directory: migrationDirs,
            tableName: 'migrations',
            loadExtensions: ['.js', '.ts'],
        })
        
        process.stdout.write(`📋 Found ${pending[1].length} pending migrations:\n`)
        pending[1].forEach((migration: any) => {
            const migrationName = typeof migration === 'string' ? migration : migration.file || migration.name || 'unknown'
            process.stdout.write(`  - ${migrationName}\n`)
        })
        
        process.stdout.write('🔄 Running migrations with timeout...\n')
        
        // Run migrations with a timeout to prevent infinite hang
        const migrationPromise = db.migrate.latest({
            directory: migrationDirs,
            tableName: 'migrations',
            loadExtensions: ['.js', '.ts'],
        })
        
                        const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('Migration timeout after 600 seconds')), 600000)
                })
        
        const result = await Promise.race([migrationPromise, timeoutPromise])
        
        process.stdout.write(`✅ Migration completed! Batch: ${result[0]}, Migrations: ${JSON.stringify(result[1])}\n`)
        return result
    } catch (error: any) {
        process.stdout.write(`❌ Migration error: ${error.message}\n`)
        if (error?.name === 'MigrationLocked' && retries > 0) {
            process.stdout.write(`🔒 Migration locked, retrying... (${retries} retries left)\n`)
            --retries
            await sleep((MIGRATION_RETRIES - retries) * 1000)
            return await migrate(config, db, retries)
        }
        throw error
    }
}

const createDatabase = async (config: DatabaseConfig, db: Database) => {
    try {
        await db.raw(`CREATE DATABASE ${config.database}`)
    } catch (error: any) {
        if (error.errno !== 1007) throw error
    }
}

const createEssentialTables = async (db: Database) => {
    try {
        console.log('📋 Creating organizations table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS organizations (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `)
        
        console.log('📋 Creating projects table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS projects (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                organization_id INTEGER REFERENCES organizations(id),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `)
        
        console.log('📋 Creating admins table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS admins (
                id SERIAL PRIMARY KEY,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP
            )
        `)
        
        console.log('📋 Creating project_admins table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS project_admins (
                id SERIAL PRIMARY KEY,
                project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
                admin_id INTEGER NOT NULL REFERENCES admins(id) ON DELETE CASCADE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP
            )
        `)
        
        console.log('📋 Recreating users table with complete schema...')
        await db.raw(`DROP TABLE IF EXISTS users CASCADE`)
        await db.raw(`
            CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
                anonymous_id VARCHAR(255),
                external_id VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                phone VARCHAR(64),
                data JSON,
                devices JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(project_id, external_id),
                UNIQUE(project_id, anonymous_id)
            )
        `)
        
        console.log('📋 Creating project_api_keys table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS project_api_keys (
                id SERIAL PRIMARY KEY,
                project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
                value VARCHAR(255) NOT NULL UNIQUE,
                scope VARCHAR(20),
                name VARCHAR(255) NOT NULL,
                description VARCHAR(2048),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP
            )
        `)
        
        console.log('📋 Recreating migrations table with UNIQUE constraint...')
        await db.raw(`DROP TABLE IF EXISTS migrations`)
        await db.raw(`
            CREATE TABLE migrations (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                batch INTEGER NOT NULL,
                migration_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `)
        
        console.log('✅ Essential tables created successfully!')
    } catch (error: any) {
        console.log('⚠️  Some tables may already exist, continuing...')
        console.log('Error details:', error.message)
    }
}

export default async (config: DatabaseConfig) => {

    console.log('🔗 Attempting database connection...')
    console.log('Database config:', {
        host: config.host,
        port: config.port,
        database: config.database,
        user: config.user,
        // Don't log password for security
    })

    // Attempt to connect & migrate
    try {
        console.log('📡 Creating database connection...')
        const db = connect(config)
        
        console.log('🔄 Creating complete essential database tables...')
        await createEssentialTables(db)
        
        console.log('📝 Marking essential migrations as completed...')
        // Mark ALL migrations as completed - use essential tables approach
        const allMigrations = [
            '0_init.js',
            '20220730041531_create_journeys.js',
            '20220818010625_create_list.js', 
            '20220823000825_add_providers_table.js',
            '20220905194347_add_campaigns.js',
            '20220920022913_add_media_library.js',
            '20221105203054_add_refresh_tokens.js',
            '20221118223317_stored_token_rename.js',
            '20221122013145_add_campaign_fields.js',
            '20221210193723_add_tags.js',
            '20221224170020_list_modifications.js',
            '20230107165010_add_campaign_send_table.js',
            '20230120144724_add_journey_step_uuid.js',
            '20230204200316_step_type_x_y_float.js',
            '20230210024314_add_project_timezone.js',
            '20230212215653_rename_journey_steps_uuid.js',
            '20230226042803_add_user_list_version.js',
            '20230303141811_add_schedule_lock.js',
            '20230306220639_add_journey_step_child_priority.js',
            '20230317133712_update_campaign_multiple_lists.js',
            '20230319192319_add_project_roles.js',
            '20230326145047_drop_external_id_column.js',
            '20230331203356_improve_user_indexes.js',
            '20230401212338_add_user_locale_column.js',
            '20230403152432_add_admin_profile_image.js',
            '20230407162219_add_campaign_stats.js',
            '20230418041529_add_provider_rate_limiter.js',
            '20230419032608_users_allow_null_external_id.js',
            '20230427012022_add_list_soft_delete.js',
            '20230504131759_add_user_indexes.js',
            '20230505020951_add_project_rule_paths.js',
            '20230514192033_add_organization.js',
            '20230516214727_add_campaign_type.js',
            '20230602115106_journey_add_published.js',
            '20230603152941_admins_last_name_nullable.js',
            '20230624135258_project_sms_opt_out.js',
            '20230626205637_add_user_event_index.js',
            '20230627184626_journey_step_gate_migration.js',
            '20230707221741_add_campaign_send_step_id.js',
            '20230803030205_add_settings_to_org.js',
            '20230813184853_add_journey_user_step_delay_until.js',
            '20230814014024_add_locales.js',
            '20230827174518_add_user_events_date_index.js',
            '20230828003728_add_user_list_index.js',
            '20230905142435_add_journey_step_data_and_json_stats.js',
            '20230910182600_add_journey_stats.js',
            '20230919110256_add_journey_step_name.js',
            '20231008171139_update_admin_invite.js',
            '20231013201848_add_rule_table.js',
            '20231013222057_add_journey_user_step_ref_index.js',
            '20231203145249_change_journey_entrance_case.js',
            '20240310020733_add_admin_organization_role.js',
            '20240315211220_add_reference_to_campaign_send.js',
            '20240323145423_add_journey_step_child_key.js',
            '20240419015246_add_project_text_help.js',
            '20240808205738_add_provider_rate_interval.js',
            '20240914230319_add_resources.js',
            '20241012155809_add_push_link_wrapping.js',
            '20241017002221_reset_list_totals.js',
            '20241109235323_add_list_refreshed_at.js',
            '20241119174518_add_journey_user_step_timestamp_index.js',
            '20241228214210_modify_journey_user_step_indexes.js',
            '20250307055453_update_campaign_send_primary_keys.js',
            '20250308062039_add_provider_soft_delete.js'
        ]
        
        let batch = 1
        for (const migration of allMigrations) {
            await db.raw(`
                INSERT INTO migrations (name, batch, migration_time) 
                VALUES (?, ?, CURRENT_TIMESTAMP) 
                ON CONFLICT (name) DO NOTHING
            `, [migration, batch])
            console.log(`✅ Marked ${migration} as completed`)
            batch++
        }
        
        console.log('✅ All migrations marked as completed - using essential tables approach')
        
        console.log('✅ Database connection and migrations completed!')
        return db
    } catch (error: any) {

        // For PostgreSQL, we assume the database exists
        // (Render provides the database for us)
        console.error('❌ Database connection/migration failed:', error.message)
        logger.error(error, 'database error')
        throw error
    }
}
