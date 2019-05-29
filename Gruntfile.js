module.exports = function(grunt) {
    'use strict';

    // Force use of Unix newlines
    grunt.util.linefeed = '\n';

    // Find what the current theme's directory is, relative to the WordPress root
    var path = process.cwd();
    path = path.replace(/^[\s\S]+\/wp-content/, "\/wp-content");

    var CSS_LESS_FILES = {
        'css/lroundups.css': 'less/lroundups.less',
        'css/lroundups-admin.css': 'less/lroundups-admin.less',
        'css/lroundups-editor.css': 'less/lroundups-editor.less'
    };

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        less: {
            development: {
                options: {
                    paths: ['less'],
                    sourceMap: true,
                    outputSourceFiles: true,
                    sourceMapBasepath: path,
                },
                files: CSS_LESS_FILES
            },
        },
        cssmin: {
            target: {
                options: {
                    report: 'gzip'
                },
                files: [{
                    expand: true,
                    cwd: 'css',
                    src: ['*.css', '!*.min.css'],
                    dest: 'css',
                    ext: '.min.css'
                }]
            }
        },
        uglify: {
            target: {
                options: {
                    report: 'gzip'
                },
                files: [{
                    expand: true,
                    cwd: 'js',
                    src: [
                        'links-common.js',
                        'lroundups-editor.js',
                        'lroundups.js',
                        '!*.min.js'
                    ],
                    dest: 'js',
                    ext: '.min.js'
                }]
            }
        },
        watch: {
            less: {
                files: [
                    'less/**/*.less',
                ],
                tasks: [
                    'less:development',
                    'uglify',
                    'cssmin'
                ]
            },
        },
        shell: {
            pot: {
                command: [
                    'wp i18n make-pot . lang/link-roundups.pot --exclude="release"'
                ].join('&&'),
                options: {
                    stdout: true
                }
            }
        },
        po2mo: {
            files: {
                src: 'lang/*.po',
                expand: true
            }
        }
    });

    grunt.registerTask('pot', ['shell:pot']);
    grunt.registerTask('build', 'build assets and language files', [
        'less',
        'cssmin',
        'uglify',
        'pot',
        'po2mo'
    ]);

    require('load-grunt-tasks')(grunt, { scope: 'devDependencies' });
}
