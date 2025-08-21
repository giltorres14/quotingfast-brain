@if(request()->get('list_id') == '112' || (isset($lead) && $lead->vici_list_id == 112))
<div style="position: fixed; top: 60px; left: 0; right: 0; z-index: 9999; background: linear-gradient(135deg, #fbbf24, #f59e0b); padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-bottom: 3px solid #d97706;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <h2 style="color: white; margin: 0 0 10px 0; font-size: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
            🎯 SPECIAL SCRIPT: Rate Reduction Reactivation
        </h2>
        <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 10px;">
            <p style="font-size: 18px; color: #d97706; font-weight: bold; margin: 0 0 10px 0;">
                ⚡ OPENING (Use Different Tone - Friendly/Informative):
            </p>
            <div style="background: #fef3c7; padding: 15px; border-left: 4px solid #f59e0b; font-size: 16px; line-height: 1.6;">
                "Hi [NAME], this is [AGENT] calling back from the Insurance Review Department. 
                <br><br>
                <strong>I know we spoke a while back and the timing wasn't right</strong>, but I'm reaching out because 
                <strong style="color: #d97706;">we've had a significant rate reduction in [STATE] area</strong> - 
                some clients are seeing 20-30% savings with the new carrier programs.
                <br><br>
                I wanted to make sure you had a chance to see if you qualify for these lower rates before they change again. 
                <strong>Do you have just 2 minutes to see what the new rates would be for you?</strong>"
            </div>
            
            <div style="margin-top: 15px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: #dcfce7; padding: 12px; border-radius: 6px;">
                    <strong style="color: #16a34a;">If YES:</strong><br>
                    "Great! Let me just verify your information is still current..."
                    <br>[Proceed with normal qualification]
                </div>
                <div style="background: #fee2e2; padding: 12px; border-radius: 6px;">
                    <strong style="color: #dc2626;">If NO/Not Interested:</strong><br>
                    "No problem! Would you prefer I send you the rate comparison by email/text?"
                    <br>[Try for soft conversion]
                </div>
            </div>
            
            <div style="margin-top: 15px; background: #e0e7ff; padding: 12px; border-radius: 6px;">
                <strong>💡 KEY POINTS TO EMPHASIZE:</strong>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>This is a DIFFERENT PROGRAM than before</li>
                    <li>Rates have DROPPED in their area</li>
                    <li>Limited time to lock in lower rates</li>
                    <li>Just checking if they qualify - no pressure</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endif




