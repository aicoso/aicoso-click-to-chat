module.exports = function(grunt) {
	'use strict';

	const { execSync } = require('child_process');
	const os = require('os');
	const path = require('path');
	const fs = require('fs');

	// Detect PHP path (for Local by Flywheel on Windows)
	let phpPath = 'php'; // default
	if (os.platform() === 'win32') {
		const localPhpPath = path.join(os.homedir(), 'AppData', 'Roaming', 'Local', 'lightning-services', 'php-8.2.27+1', 'bin', 'win64', 'php.exe');
		if (fs.existsSync(localPhpPath)) {
			phpPath = `"${localPhpPath}"`; // Quote the path for Windows
		}
	}

	// Custom task to generate POT file using PHP directly
	grunt.registerTask('makepot', 'Generate POT translation file', function() {
		const done = this.async();

		grunt.log.writeln('Generating POT file...');

		// Use the PHP from node-wp-i18n
		const makepotScript = path.join(__dirname, 'node_modules', 'node-wp-i18n', 'bin', 'php', 'node-makepot.php');
		const outputFile = path.join(__dirname, 'languages', 'aicoso-click-to-chat.pot');

		// Ensure languages directory exists
		if (!fs.existsSync(path.join(__dirname, 'languages'))) {
			fs.mkdirSync(path.join(__dirname, 'languages'));
		}

		try {
			const cmd = `${phpPath} "${makepotScript}" wp-plugin "${__dirname}" "${outputFile}" aicoso-click-to-chat aicoso-click-to-chat.php "node_modules/.*,vendor/.*,.git/.*,.github/.*,build/.*,tests/.*" ""`;

			grunt.log.writeln(`Running: ${cmd}`);
			execSync(cmd, { stdio: 'inherit' });

			grunt.log.ok('POT file generated successfully at languages/aicoso-click-to-chat.pot');
			done(true);
		} catch (error) {
			grunt.log.error('Failed to generate POT file');
			grunt.log.error(error.message);
			done(false);
		}
	});

	// Register default task
	grunt.registerTask('default', ['makepot']);
	grunt.registerTask('i18n', ['makepot']);
};
