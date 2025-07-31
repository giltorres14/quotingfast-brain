exports.up = async function(knex) {
    // Check if we need to do this migration (defensive check)
    const hasIdColumn = await knex.schema.hasColumn('campaign_sends', 'id')
    
    if (hasIdColumn) {
        // First, ensure the new primary key columns exist and are not null
        const hasCampaignId = await knex.schema.hasColumn('campaign_sends', 'campaign_id')
        const hasUserId = await knex.schema.hasColumn('campaign_sends', 'user_id') 
        const hasReferenceId = await knex.schema.hasColumn('campaign_sends', 'reference_id')
        
        if (hasCampaignId && hasUserId && hasReferenceId) {
            // Make sure all rows have valid data for the new primary key
            await knex.raw('UPDATE campaign_sends SET campaign_id = COALESCE(campaign_id, 0), user_id = COALESCE(user_id, 0), reference_id = COALESCE(reference_id, \'0\') WHERE campaign_id IS NULL OR user_id IS NULL OR reference_id IS NULL')
            
            // Drop the unique constraint first if it exists
            try {
                await knex.raw('ALTER TABLE campaign_sends DROP INDEX campaign_sends_user_id_campaign_id_reference_id_unique')
            } catch (e) {
                // Index might not exist, continue
            }
            
            // Do the primary key change in one atomic operation
            await knex.raw('ALTER TABLE campaign_sends DROP PRIMARY KEY, DROP COLUMN id, ADD PRIMARY KEY (campaign_id, user_id, reference_id)')
            
            // Add the index
            await knex.schema.alterTable('campaign_sends', table => {
                table.index('user_id', 'campaign_sends_user_id_foreign')
            })
        }
    }
}

exports.down = async function(knex) {
    await knex.schema.alterTable('campaign_sends', table => {
        table.unique(['user_id', 'campaign_id', 'reference_id'])
    })

    await knex.raw('alter table campaign_sends drop primary key')

    await knex.schema.alterTable('campaign_sends', table => {
        table.increments('id')
    })

    await knex.raw('alter table campaign_sends add primary key (id)')
}
