'use strict';
module.exports = function(grunt) {

	// load all tasks
	require('load-grunt-tasks')(grunt, {scope: 'devDependencies'});

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		watch: {
			scripts: {
				files: ['js/title-toggle.js'],
				tasks: ['uglify'],
				options: {
				  spawn: false,
				},
			}
		},
		uglify: {
			options: {
				banner: '/*! <%= pkg.title %> <%= pkg.version %> */\n',
				drop_console: true
			},
			app: {
				files: {
					'js/title-toggle.min.js' : 'js/title-toggle.js',
				}
			},
		},
		// https://www.npmjs.org/package/grunt-wp-i18n
		makepot: {
			target: {
				options: {
					domainPath: 'languages/',
					potFilename: '<%= pkg.name %>.pot',
					potHeaders: {
					poedit: true, // Includes common Poedit headers.
					'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
				},
				type: 'wp-plugin',
				updateTimestamp: false,
				processPot: function( pot, options ) {
					pot.headers['report-msgid-bugs-to'] = 'https://kathyisawesome.com/contact';
					pot.headers['language'] = 'en_US';
					return pot;
					}
				}
			}
		},
		replace: {
			pluginVersion: {
				src: [
					'<%= pkg.name %>.php',
					'style.css'
				],
				overwrite: true,
				replacements: [{
					from: /Version:.*$/m,
					to: 'Version: <%= pkg.version %>'
				}]
			},
			readMeVersion: {
				src: [
					'readme.txt',
					'readme.md'
				],
				overwrite: true,
				replacements: [ {
					from: /Stable tag:.*$/m,
					to: 'Stable tag: <%= pkg.version %>'
				} ]
			},
		},
		'ftp-deploy': {
			build: {
				auth: {
					host: 'ftp.criticalink.org',
					port: 21,
					authKey: 'key1'
				},
				src: "D:/VVV/www/criticalink/wp-content/plugins/title-toggle",
				dest: '/sandbox/wp-content/plugins/<%= pkg.name %>',
				exclusions: [".*", "wp-config.php", "node_modules", "sftp-config.json", "sftp-settings.json", "venv", "_darcs", "CVS", ".DS_Store", "Thumbs.db", "desktop.ini"]
			}
		}

	});

	grunt.registerTask( 'default', [
		'uglify',
	]);


	grunt.registerTask( 'release', [
		'replace',
		'uglify',
		'makepot'
	]);

};
