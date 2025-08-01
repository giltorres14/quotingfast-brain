import App from './app'
import env from './config/env'

console.log('🚀 Starting Parcelvoy boot process...')

export default App.init(env())
    .then(app => {
        console.log('✅ App initialized successfully, starting services...')
        return app.start()
    })
    .then(app => {
        console.log('🎉 App started successfully!')
        return app
    })
    .catch(error => {
        console.error('❌ Boot process failed:', error)
        console.error('Stack trace:', error.stack)
        process.exit(1)
    })
