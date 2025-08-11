#!/usr/bin/env python3
from PIL import Image, ImageDraw, ImageFont
import os

# Create a 64x64 image with purple background
size = (64, 64)
background_color = (107, 70, 193)  # Purple (#6B46C1)
text_color = (255, 255, 255)  # White

# Create image
img = Image.new('RGB', size, background_color)
draw = ImageDraw.Draw(img)

# Try to use a bold font, fallback to default if not available
try:
    # Try to use a system font
    font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 32)
except:
    # Use default font if system font not found
    font = ImageFont.load_default()

# Draw "QF" text
text = "QF"
# Get text size
bbox = draw.textbbox((0, 0), text, font=font)
text_width = bbox[2] - bbox[0]
text_height = bbox[3] - bbox[1]

# Center the text
x = (size[0] - text_width) // 2
y = (size[1] - text_height) // 2 - 5  # Slight adjustment

draw.text((x, y), text, fill=text_color, font=font)

# Save the favicon
img.save('public/favicon_new.png')
print("Favicon created: public/favicon_new.png")

# Also create a 16x16 version for better compatibility
img_small = img.resize((16, 16), Image.Resampling.LANCZOS)
img_small.save('public/favicon_16.png')
print("Small favicon created: public/favicon_16.png")

# And a 32x32 version
img_medium = img.resize((32, 32), Image.Resampling.LANCZOS)
img_medium.save('public/favicon_32.png')
print("Medium favicon created: public/favicon_32.png")
