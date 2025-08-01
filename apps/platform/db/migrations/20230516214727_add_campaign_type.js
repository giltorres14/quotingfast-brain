exports.up = async function(knex) {
    // Check if type column already exists to avoid duplicate column errors
    const hasTypeColumn = await knex.schema.hasColumn('campaigns', 'type')
    
    if (!hasTypeColumn) {
        await knex.schema.table('campaigns', function(table) {
            table.string('type', 255)
        })
    }
    
    // Only update if we have rows that need updating
    await knex.raw('UPDATE campaigns SET type = CASE WHEN list_ids IS NULL THEN \'trigger\' ELSE \'blast\' END WHERE type IS NULL')
}

exports.down = async function(knex) {
    await knex.schema.table('campaigns', function(table) {
        table.dropColumn('type')
    })
}
