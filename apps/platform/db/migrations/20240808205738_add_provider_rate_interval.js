exports.up = async function(knex) {
    await knex.schema.table('providers', function(table) {
        table.string('rate_interval', 12).defaultTo('second')
    })
}

exports.down = async function(knex) {
    await knex.schema.table('providers', function(table) {
        table.dropColumn('rate_interval')
    })
}
