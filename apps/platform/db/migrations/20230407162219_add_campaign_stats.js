exports.up = async function(knex) {
    await knex.schema.table('campaign_sends', function(table) {
        table.integer('clicks').defaultTo(0)
        table.timestamp('opened_at').nullable()
    })
}

exports.down = async function(knex) {
    await knex.schema.table('campaign_sends', function(table) {
        table.dropColumn('clicks')
        table.dropColumn('opened_at')
    })
}
