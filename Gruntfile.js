module.exports = function(grunt) {
    'use strict';

    // Force use of Unix newlines
    grunt.util.linefeed = '\n';

    // Find what the current theme's directory is, relative to the WordPress root
    var path = process.cwd();
    path = path.replace(/^[\s\S]+\/wp-content/, "\/wp-content");

    var CSS_LESS_FILES = {
        'css/link-roundups.css': 'less/link-roundups.less',
        'css/saved-links-common.css': 'less/saved-links-common.less',
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

        uglify: {
            target: {
                options: {
                    report: 'gzip'
                },
                files: [{
                    expand: true,
                    cwd: 'js',
                    src: [
                        'custom-less-variables.js',
                        'custom-sidebar.js',
                        'custom-term-icons.js',
                        'featured-media.js',
                        'image-widget.js',
                        'largoCore.js',
                        'load-more-posts.js',
                        'top-terms.js',
                        'update-page.js',
                        'widgets-php.js',
                        '!*.min.js'
                    ],
                    dest: 'js',
                    ext: '.min.js'
                }]
            }
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

        watch: {
            less: {
                files: [
                    'less/**/*.less',
                ],
                tasks: [
                    'less:development',
                    'cssmin'
                ]
            },
        }
    });

    require('load-grunt-tasks')(grunt, { scope: 'devDependencies' });
}
