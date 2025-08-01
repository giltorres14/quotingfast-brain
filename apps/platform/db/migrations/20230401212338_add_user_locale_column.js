exports.up = async function(knex) {
    await knex.schema
        .table('users', function(table) {
            table.string('locale')
        })
}

exports.down = async function(knex) {
    await knex.schema
        .table('users', function(table) {
            table.dropColumn('locale')
        })
}
