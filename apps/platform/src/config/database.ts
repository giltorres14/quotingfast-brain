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
                    setTimeout(() => reject(new Error('Migration timeout after 300 seconds')), 300000)
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
        
        console.log('📋 Creating users table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                external_id VARCHAR(255),
                email VARCHAR(255),
                project_id INTEGER REFERENCES projects(id),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `)
        
        console.log('📋 Creating migrations table...')
        await db.raw(`
            CREATE TABLE IF NOT EXISTS migrations (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
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
        
        console.log('🔄 Creating essential database tables...')
        await createEssentialTables(db)
        
        console.log('✅ Database connection and essential tables created!')
        return db
    } catch (error: any) {

        // For PostgreSQL, we assume the database exists
        // (Render provides the database for us)
        console.error('❌ Database connection/migration failed:', error.message)
        logger.error(error, 'database error')
        throw error
    }
}
