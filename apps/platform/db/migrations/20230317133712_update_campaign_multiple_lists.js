exports.up = async function(knex) {
    // Check if columns already exist to avoid duplicate column errors
    const hasExclusionListIds = await knex.schema.hasColumn('campaigns', 'exclusion_list_ids')
    const hasListIds = await knex.schema.hasColumn('campaigns', 'list_ids')
    
    if (!hasExclusionListIds || !hasListIds) {
        await knex.schema.table('campaigns', function(table) {
            if (!hasExclusionListIds) {
                table.json('exclusion_list_ids').after('list_id')
            }
            if (!hasListIds) {
                table.json('list_ids').after('list_id')
            }
        })
    }
    
    // Only update if list_ids column exists and list_id still exists
    const hasListId = await knex.schema.hasColumn('campaigns', 'list_id')
    if (hasListIds && hasListId) {
        await knex.raw('UPDATE campaigns SET list_ids = CONCAT(\'[\', campaigns.list_id, \']\') WHERE list_ids IS NULL')
    }
    
    // Only drop if list_id column still exists
    if (hasListId) {
        await knex.schema.table('campaigns', function(table) {
            table.dropForeign('list_id')
            table.dropColumn('list_id')
        })
    }
}

exports.down = async function(knex) {
    await knex.schema.table('campaigns', function(table) {
        table.integer('list_id')
            .unsigned()
            .references('id')
            .inTable('lists')
            .onDelete('CASCADE')
            .after('project_id')
        table.dropColumn('list_ids')
        table.dropColumn('exclusion_list_ids')
    })
}
