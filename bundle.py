import os

# Directories strictly needed for architectural review
INCLUDE_DIRS = [
    'app',
    'config',
    'database/migrations',
    'routes',
    'resources/js',
    'resources/css',
    'resources/views',
    'docker'
]

# Specific root files needed
INCLUDE_FILES = [
    '.env',
    '.env.example',
    'docker-compose.yml',
    'vite.config.js',
    'composer.json'
]

# Blacklist to prevent massive files from freezing the output
IGNORE_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'bootstrap', 'public']

OUTPUT_FILE = 'NEXUS_CONTEXT.txt'

def generate_bundle():
    print("Initiating Nexus Context Extraction...")
    with open(OUTPUT_FILE, 'w', encoding='utf-8') as outfile:
        outfile.write("# NEXUS OS - ARCHITECTURE BUNDLE\n\n")

        # 1. Grab Root Files
        for file in INCLUDE_FILES:
            if os.path.exists(file):
                _write_file(file, outfile)

        # 2. Grab Directories Recursively
        for directory in INCLUDE_DIRS:
            if os.path.exists(directory):
                for root, dirs, files in os.walk(directory):
                    # Filter out blacklisted directories
                    dirs[:] = [d for d in dirs if d not in IGNORE_DIRS]

                    for file in files:
                        # Only grab code files
                        if file.endswith(('.php', '.js', '.css', '.vue', '.sql', '.yml', '.yaml')):
                            file_path = os.path.join(root, file)
                            _write_file(file_path, outfile)

    print(f"✅ Extraction Complete! Saved to {OUTPUT_FILE}")

def _write_file(filepath, outfile):
    try:
        with open(filepath, 'r', encoding='utf-8') as infile:
            content = infile.read()
            outfile.write(f"## FILE: {filepath}\n")
            outfile.write("```\n")
            outfile.write(content)
            outfile.write("\n```\n\n")
            print(f"  -> Bundled: {filepath}")
    except Exception as e:
        print(f"  ⚠️ Skipped {filepath}: {e}")

if __name__ == "__main__":
    generate_bundle()
