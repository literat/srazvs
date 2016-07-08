module.exports = function (grunt) {

	// Autoload all Grunt tasks
	require('matchdep').filterAll('grunt-*').forEach(grunt.loadNpmTasks);

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		// Meta informations
		meta: {
			now: grunt.template.today('ddd, d mmm yyyy h:MM:ss'),
			files: {
				bin: {
					tester: 'vendor/bin/tester',
				},
			},
		},

		nette_tester: {
			options: {
				bin: 'vendor/bin/tester',
				jobs: 10,
				quiet: false,
				phpIni: 'tests/php.ini',
			},
			src: ['tests'],
		},

		shell: {
			nette_tester: {
				command: 'php -f vendor/nette/tester/Tester/tester.php -- -c tests/php.ini -j 10 tests',
			},
			ftpDeployment: {
				command: 'php -f vendor/bin/deployment deployment.ini',
			},
			ftpDeploymentTest: {
				command: 'php -f vendor/bin/deployment deployment.ini --test',
			}
		},

		clean: {
			container: ['temp/Container_*'],
			cache: ['temp/cache/_*'],
		},

		conventionalChangelog: {
			options: {
				changelogOpts: {
					// conventional-changelog options go here
					preset: 'angular'
				},
				context: {
					// context goes here
				},
				gitRawCommitsOpts: {
					// git-raw-commits options go here
				},
				parserOpts: {
					// conventional-commits-parser options go here
				},
				writerOpts: {
					// conventional-changelog-writer options go here
				}
			},
			release: {
				src: 'CHANGELOG.md'
			}
		},

		bump: {
			options: {
				files: [
					'package.json',
				],
				updateConfigs: ['pkg'],
				commitFiles: [
					'package.json',
					'CHANGELOG.md',
				],
				commitMessage: 'Release v%VERSION%',
				createTag: true,
				tagName: 'v%VERSION%',
				tagMessage: 'Release v%VERSION%',
				push: true,
				pushTo: 'origin',
			}
		},

		// Watch task
		watch: {
			php: {
				files: ['**/*.php'],
				tasks: ['test'],
			},
		},

	});

	grunt.registerTask('build', 'Bumps version and builds JS.', function(version_type) {
		if (version_type !== 'patch' && version_type !== 'minor' && version_type !== 'major') {
		version_type = 'minor';
		}
		return grunt.task.run([
			"bump-only:" + version_type,
			'conventionalChangelog',
			'bump-commit',
		]);
	});

	grunt.registerTask('deploy', 'Deploy files to server over FTP.', function(account) {
		if (typeof account !== 'undefined') {
			return grunt.task.run(['ftp-deploy:' + account]);
		} else {
			return grunt.task.run([
				'shell:nette_tester',
				'shell:ftpDeployment',
			]);
		}
	});

	grunt.registerTask('test', [
		'shell:nette_tester',
	]);

	grunt.registerTask('dev', [
	]);

	grunt.registerTask('default', 'watch');
};
