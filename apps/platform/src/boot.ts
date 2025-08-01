import App from './app'
import env from './config/env'

// Force immediate output
process.stdout.write('🚀 Starting Parcelvoy boot process...\n')
process.stdout.write('📊 Loading environment configuration...\n')

let envConfig
try {
    envConfig = env()
    process.stdout.write('✅ Environment loaded successfully\n')
} catch (error) {
    process.stdout.write('❌ Environment loading failed: ' + error.message + '\n')
    process.exit(1)
}

process.stdout.write('🔧 Initializing app...\n')

export default App.init(envConfig)
    .then(app => {
        process.stdout.write('✅ App initialized successfully, starting services...\n')
        return app.start()
    })
    .then(app => {
        process.stdout.write('🎉 App started successfully!\n')
        return app
    })
    .catch(error => {
        process.stdout.write('❌ Boot process failed: ' + error.message + '\n')
        process.stdout.write('Stack trace: ' + error.stack + '\n')
        process.exit(1)
    })
