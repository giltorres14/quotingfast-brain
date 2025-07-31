exports.up = async function(knex) {
    // Check if columns already exist to avoid duplicate column errors
    const hasReferenceType = await knex.schema.hasColumn('campaign_sends', 'reference_type')
    const hasReferenceId = await knex.schema.hasColumn('campaign_sends', 'reference_id')
    
    if (!hasReferenceType || !hasReferenceId) {
        await knex.schema.alterTable('campaign_sends', function(table) {
            if (!hasReferenceType) {
                table.string('reference_type')
            }
            if (!hasReferenceId) {
                table.string('reference_id')
                    .notNullable()
                    .defaultTo('0')
            }
        })
    }

    // Only update if we have rows that need updating and user_step_id column exists
    const hasUserStepId = await knex.schema.hasColumn('campaign_sends', 'user_step_id')
    if (hasUserStepId && hasReferenceId && hasReferenceType) {
        await knex.raw('UPDATE campaign_sends SET reference_id = user_step_id, reference_type = \'journey\' WHERE reference_type IS NULL')
    }

    // Only modify constraints if user_step_id column still exists
    if (hasUserStepId) {
        await knex.schema.alterTable('campaign_sends', function(table) {
            table.unique(['user_id', 'campaign_id', 'reference_id'])
            table.dropUnique(['user_id', 'campaign_id', 'user_step_id'])
            table.dropColumn('user_step_id')
        })
    }
}

exports.down = async function(knex) {
    await knex.schema.alterTable('campaign_sends', function(table) {
        table.integer('user_step_id')
            .unsigned()
            .notNullable()
            .defaultTo(0)
        table.unique(['user_id', 'campaign_id', 'user_step_id'])
    })

    await knex.raw('UPDATE campaign_sends SET user_step_id = reference_id')

    await knex.schema.alterTable('campaign_sends', function(table) {
        table.dropUnique(['user_id', 'campaign_id', 'reference_id'])
        table.dropColumn('reference_id')
        table.dropColumn('reference_type')
    })
}
