exports.up = function(knex) {
    return knex.schema
        .table('campaigns', function(table) {
            table.timestamp('send_at')
            table.json('delivery')
            table.string('state', 20)
        })
}

exports.down = function(knex) {
    return knex.schema
        .table('campaigns', function(table) {
            table.dropColumn('send_at')
            table.dropColumn('delivery')
            table.dropColumn('state')
        })
}
