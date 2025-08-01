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
        return await db.migrate.latest({
            directory: [
                path.resolve(__dirname, '../../db/migrations'),
                ...config.migrationPaths,
            ],
            tableName: 'migrations',
            loadExtensions: ['.js', '.ts'],
        })
    } catch (error: any) {
        if (error?.name === 'MigrationLocked' && retries > 0) {
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
        
        console.log('🔄 Running database migrations...')
        await migrate(config, db)
        
        console.log('✅ Database connection and migration successful!')
        return db
    } catch (error: any) {

        // For PostgreSQL, we assume the database exists
        // (Render provides the database for us)
        console.error('❌ Database connection/migration failed:', error.message)
        logger.error(error, 'database error')
        throw error
    }
}
