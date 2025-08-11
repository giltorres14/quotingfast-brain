<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Picker - Choose Your Blue</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #1f2937;
        }
        
        .current-color {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .current-color-display {
            width: 200px;
            height: 80px;
            margin: 10px auto;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .color-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .color-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .color-section h2 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #374151;
            font-weight: 600;
        }
        
        .color-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border: 2px solid transparent;
        }
        
        .color-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
            border-color: #e5e7eb;
        }
        
        .color-card.selected {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        
        .color-display {
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .color-info {
            padding: 12px;
            background: white;
        }
        
        .color-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
            font-size: 14px;
        }
        
        .color-hex {
            color: #6b7280;
            font-family: monospace;
            font-size: 13px;
        }
        
        .selected-info {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            min-width: 300px;
            z-index: 1000;
        }
        
        .selected-info h3 {
            margin-bottom: 10px;
            color: #1f2937;
        }
        
        .copy-btn {
            background: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            margin-top: 10px;
            width: 100%;
        }
        
        .copy-btn:hover {
            background: #059669;
        }
        
        .preview-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .preview-header {
            padding: 15px;
            border-radius: 8px;
            color: white;
            margin-bottom: 15px;
        }
        
        .preview-button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .custom-color-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .custom-color-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .custom-color-input input[type="color"] {
            width: 60px;
            height: 40px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .custom-color-input input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Choose Your Perfect Blue</h1>
        
        <div class="current-color">
            <h3>Current Color</h3>
            <div class="current-color-display" style="background: #2563eb;">
                #2563eb
            </div>
            <p style="color: #6b7280; margin-top: 10px;">This is the current primary blue used in The Brain</p>
        </div>
        
        <!-- Custom Color Picker -->
        <div class="custom-color-section">
            <h2>🎯 Custom Color Picker</h2>
            <div class="custom-color-input">
                <input type="color" id="customColorPicker" value="#2563eb">
                <input type="text" id="customColorHex" value="#2563eb" placeholder="#000000">
                <button class="copy-btn" onclick="selectColor(document.getElementById('customColorHex').value, 'Custom')" style="width: auto;">
                    Use This Color
                </button>
            </div>
        </div>
        
        <!-- Preview Section -->
        <div class="preview-section">
            <h2>👁️ Live Preview</h2>
            <div class="preview-header" id="previewHeader" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                <h3>The Brain - Lead Management System</h3>
                <p>See how your selected color looks in action</p>
            </div>
            <div>
                <button class="preview-button" id="previewBtn1" style="background: #2563eb;">Primary Button</button>
                <button class="preview-button" id="previewBtn2" style="background: #2563eb; opacity: 0.9;">Secondary Button</button>
                <button class="preview-button" id="previewBtn3" style="background: #2563eb; opacity: 0.7;">Tertiary Button</button>
            </div>
        </div>
        
        <!-- Popular Blues -->
        <div class="color-section">
            <h2>🌟 Popular Blues</h2>
            <div class="color-grid">
                <div class="color-card" onclick="selectColor('#3b82f6', 'Blue 500')">
                    <div class="color-display" style="background: #3b82f6;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Blue 500</div>
                        <div class="color-hex">#3b82f6</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#2563eb', 'Blue 600')">
                    <div class="color-display" style="background: #2563eb;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Blue 600</div>
                        <div class="color-hex">#2563eb</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#1d4ed8', 'Blue 700')">
                    <div class="color-display" style="background: #1d4ed8;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Blue 700</div>
                        <div class="color-hex">#1d4ed8</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#1e40af', 'Blue 800')">
                    <div class="color-display" style="background: #1e40af;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Blue 800</div>
                        <div class="color-hex">#1e40af</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Indigo Shades (Current Family) -->
        <div class="color-section">
            <h2>💜 Indigo Shades (Current Color Family)</h2>
            <div class="color-grid">
                <div class="color-card selected" onclick="selectColor('#2563eb', 'Indigo 600 (Current)')">
                    <div class="color-display" style="background: #2563eb;">Current</div>
                    <div class="color-info">
                        <div class="color-name">Indigo 600 (Current)</div>
                        <div class="color-hex">#2563eb</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#3b82f6', 'Indigo 500')">
                    <div class="color-display" style="background: #3b82f6;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Indigo 500</div>
                        <div class="color-hex">#3b82f6</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#1d4ed8', 'Indigo 700')">
                    <div class="color-display" style="background: #1d4ed8;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Indigo 700</div>
                        <div class="color-hex">#1d4ed8</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#1e40af', 'Indigo 800')">
                    <div class="color-display" style="background: #1e40af;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Indigo 800</div>
                        <div class="color-hex">#1e40af</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sky Blues -->
        <div class="color-section">
            <h2>☁️ Sky Blues</h2>
            <div class="color-grid">
                <div class="color-card" onclick="selectColor('#38bdf8', 'Sky 400')">
                    <div class="color-display" style="background: #38bdf8;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Sky 400</div>
                        <div class="color-hex">#38bdf8</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0ea5e9', 'Sky 500')">
                    <div class="color-display" style="background: #0ea5e9;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Sky 500</div>
                        <div class="color-hex">#0ea5e9</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0284c7', 'Sky 600')">
                    <div class="color-display" style="background: #0284c7;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Sky 600</div>
                        <div class="color-hex">#0284c7</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0369a1', 'Sky 700')">
                    <div class="color-display" style="background: #0369a1;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Sky 700</div>
                        <div class="color-hex">#0369a1</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cyan/Teal Blues -->
        <div class="color-section">
            <h2>🌊 Cyan & Teal Blues</h2>
            <div class="color-grid">
                <div class="color-card" onclick="selectColor('#06b6d4', 'Cyan 500')">
                    <div class="color-display" style="background: #06b6d4;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Cyan 500</div>
                        <div class="color-hex">#06b6d4</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0891b2', 'Cyan 600')">
                    <div class="color-display" style="background: #0891b2;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Cyan 600</div>
                        <div class="color-hex">#0891b2</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#14b8a6', 'Teal 500')">
                    <div class="color-display" style="background: #14b8a6;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Teal 500</div>
                        <div class="color-hex">#14b8a6</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0d9488', 'Teal 600')">
                    <div class="color-display" style="background: #0d9488;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Teal 600</div>
                        <div class="color-hex">#0d9488</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navy/Dark Blues -->
        <div class="color-section">
            <h2>🌙 Navy & Dark Blues</h2>
            <div class="color-grid">
                <div class="color-card" onclick="selectColor('#1e3a8a', 'Navy Blue')">
                    <div class="color-display" style="background: #1e3a8a;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Navy Blue</div>
                        <div class="color-hex">#1e3a8a</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#1e293b', 'Slate 800')">
                    <div class="color-display" style="background: #1e293b;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Slate 800</div>
                        <div class="color-hex">#1e293b</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0f172a', 'Slate 900')">
                    <div class="color-display" style="background: #0f172a;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Slate 900</div>
                        <div class="color-hex">#0f172a</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#172554', 'Blue 950')">
                    <div class="color-display" style="background: #172554;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Blue 950</div>
                        <div class="color-hex">#172554</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Professional Blues -->
        <div class="color-section">
            <h2>💼 Professional Blues</h2>
            <div class="color-grid">
                <div class="color-card" onclick="selectColor('#0066cc', 'Corporate Blue')">
                    <div class="color-display" style="background: #0066cc;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Corporate Blue</div>
                        <div class="color-hex">#0066cc</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#003d82', 'Business Blue')">
                    <div class="color-display" style="background: #003d82;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Business Blue</div>
                        <div class="color-hex">#003d82</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0052cc', 'Atlassian Blue')">
                    <div class="color-display" style="background: #0052cc;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Atlassian Blue</div>
                        <div class="color-hex">#0052cc</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0747a6', 'JIRA Blue')">
                    <div class="color-display" style="background: #0747a6;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">JIRA Blue</div>
                        <div class="color-hex">#0747a6</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Brand Blues -->
        <div class="color-section">
            <h2>🏢 Famous Brand Blues</h2>
            <div class="color-grid">
                <div class="color-card" onclick="selectColor('#1877f2', 'Facebook Blue')">
                    <div class="color-display" style="background: #1877f2;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Facebook Blue</div>
                        <div class="color-hex">#1877f2</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#1da1f2', 'Twitter Blue')">
                    <div class="color-display" style="background: #1da1f2;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Twitter Blue</div>
                        <div class="color-hex">#1da1f2</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#0077b5', 'LinkedIn Blue')">
                    <div class="color-display" style="background: #0077b5;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">LinkedIn Blue</div>
                        <div class="color-hex">#0077b5</div>
                    </div>
                </div>
                <div class="color-card" onclick="selectColor('#00a4ef', 'Skype Blue')">
                    <div class="color-display" style="background: #00a4ef;">Preview</div>
                    <div class="color-info">
                        <div class="color-name">Skype Blue</div>
                        <div class="color-hex">#00a4ef</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Selected Color Info -->
    <div class="selected-info" id="selectedInfo" style="display: none;">
        <h3>✅ Selected Color</h3>
        <div style="margin: 10px 0;">
            <div id="selectedColorDisplay" style="height: 60px; border-radius: 8px; margin-bottom: 10px;"></div>
            <div><strong>Name:</strong> <span id="selectedName"></span></div>
            <div><strong>Hex:</strong> <span id="selectedHex" style="font-family: monospace;"></span></div>
        </div>
        <button class="copy-btn" onclick="copyToClipboard()">
            📋 Copy Hex Code
        </button>
        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
            <p style="color: #6b7280; font-size: 14px;">
                Tell the assistant: <br>
                <strong>"Use <span id="selectedHexText"></span> as the primary color"</strong>
            </p>
        </div>
    </div>
    
    <script>
        let selectedColor = '#2563eb';
        let selectedName = 'Indigo 600 (Current)';
        
        // Custom color picker sync
        document.getElementById('customColorPicker').addEventListener('input', function(e) {
            document.getElementById('customColorHex').value = e.target.value;
            updatePreview(e.target.value);
        });
        
        document.getElementById('customColorHex').addEventListener('input', function(e) {
            if (e.target.value.match(/^#[0-9A-Fa-f]{6}$/)) {
                document.getElementById('customColorPicker').value = e.target.value;
                updatePreview(e.target.value);
            }
        });
        
        function selectColor(hex, name) {
            selectedColor = hex;
            selectedName = name;
            
            // Remove previous selection
            document.querySelectorAll('.color-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            event.currentTarget.classList.add('selected');
            
            // Update preview
            updatePreview(hex);
            
            // Show selected info
            document.getElementById('selectedInfo').style.display = 'block';
            document.getElementById('selectedColorDisplay').style.background = hex;
            document.getElementById('selectedName').textContent = name;
            document.getElementById('selectedHex').textContent = hex;
            document.getElementById('selectedHexText').textContent = hex;
        }
        
        function updatePreview(hex) {
            // Update preview header gradient
            document.getElementById('previewHeader').style.background = 
                `linear-gradient(135deg, ${hex} 0%, #1d4ed8 100%)`;
            
            // Update preview buttons
            document.getElementById('previewBtn1').style.background = hex;
            document.getElementById('previewBtn2').style.background = hex;
            document.getElementById('previewBtn3').style.background = hex;
        }
        
        function copyToClipboard() {
            navigator.clipboard.writeText(selectedColor).then(() => {
                const btn = event.currentTarget;
                const originalText = btn.innerHTML;
                btn.innerHTML = '✅ Copied!';
                btn.style.background = '#10b981';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                }, 2000);
            });
        }
    </script>
</body>
</html>

