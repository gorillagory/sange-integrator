import os
import datetime

# ==========================================
# CONFIGURATION
# ==========================================
OUTPUT_FILE = 'NEXUS_CONTEXT.txt'
IGNORE_DIRS = {'.git', 'node_modules', 'vendor', '__pycache__', 'storage', 'bootstrap', '.idea'}
VALID_EXTENSIONS = {'.js', '.php', '.css', '.html', '.json', '.md', '.py', '.gs', '.env', '.example'}

def generate_bundle():
    current_dir = os.getcwd()

    with open(OUTPUT_FILE, 'w', encoding='utf-8') as outfile:
        outfile.write("# NEXUS OS - CURRENT CODEBASE\n")
        outfile.write("Last Updated: " + datetime.datetime.now().isoformat() + "\n\n")

        for root, dirs, files in os.walk(current_dir):
            # Modify dirs in-place to skip ignored directories
            dirs[:] = [d for d in dirs if d not in IGNORE_DIRS]

            for file in files:
                ext = os.path.splitext(file)[1].lower()
                # Exclude the script itself from the bundle
                if ext in VALID_EXTENSIONS and file != os.path.basename(__file__):
                    file_path = os.path.join(root, file)
                    rel_path = os.path.relpath(file_path, current_dir)

                    try:
                        with open(file_path, 'r', encoding='utf-8') as infile:
                            content = infile.read()

                        # Write the file header and markdown block safely
                        outfile.write("## FILE: " + rel_path + "\n")
                        lang = ext[1:] if ext else 'text'
                        outfile.write("```" + lang + "\n")
                        outfile.write(content)
                        outfile.write("\n```\n\n")

                        print("Bundled: " + rel_path)
                    except Exception as e:
                        print("Skipped " + rel_path + " (Read Error: " + str(e) + ")")

if __name__ == "__main__":
    generate_bundle()
    print("✅ Codebase successfully bundled into " + OUTPUT_FILE)
