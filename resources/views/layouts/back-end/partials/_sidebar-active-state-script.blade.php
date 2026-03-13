<script>
    (function($) {
        'use strict';

        var parser = document.createElement('a');

        function normalizePath(path) {
            if (!path) {
                return '/';
            }

            path = path.replace(/\/+$/, '');
            return path === '' ? '/' : path;
        }

        function isNavigableHref(href) {
            if (!href) {
                return false;
            }

            href = $.trim(href);
            if (href === '' || href === '#' || href.indexOf('#') === 0) {
                return false;
            }

            return !/^javascript:/i.test(href);
        }

        function parseHref(href) {
            parser.href = href;

            return {
                origin: parser.protocol + '//' + parser.host,
                path: normalizePath(parser.pathname),
                search: parser.search || ''
            };
        }

        function getScore(linkPath, linkSearch, currentPath, currentSearch) {
            if (linkPath === currentPath) {
                if (linkSearch && linkSearch === currentSearch) {
                    return 10000;
                }

                if (!linkSearch) {
                    return 9000;
                }

                if (currentSearch.indexOf(linkSearch) !== -1) {
                    return 9500;
                }

                return 8500;
            }

            if (currentPath.indexOf(linkPath + '/') === 0) {
                return 7000 + linkPath.length;
            }

            if (linkPath !== '/' && currentPath.indexOf(linkPath) === 0) {
                return 6000 + linkPath.length;
            }

            return -1;
        }

        function openMenuTree($link) {
            if (!$link.length) {
                return;
            }

            $link.addClass('active');

            var $currentItem = $link.closest('li');
            $currentItem.addClass('active');

            $currentItem.parents('ul.js-navbar-vertical-aside-submenu').each(function() {
                $(this).css('display', 'block').addClass('show');
            });

            $currentItem.parents('li.navbar-vertical-aside-has-menu').each(function() {
                var $parentMenu = $(this);
                $parentMenu.addClass('active show');
                $parentMenu.children('ul.js-navbar-vertical-aside-submenu').css('display', 'block').addClass('show');
                $parentMenu.children('a.nav-link').addClass('active').attr('aria-expanded', 'true');
            });
        }

        function findBestMatch($sidebar) {
            var currentOrigin = window.location.protocol + '//' + window.location.host;
            var currentPath = normalizePath(window.location.pathname);
            var currentSearch = window.location.search || '';
            var bestElement = null;
            var bestScore = -1;

            $sidebar.find('a.nav-link[href]').each(function() {
                var href = $(this).attr('href');

                if (!isNavigableHref(href)) {
                    return;
                }

                var parsed = parseHref(href);
                if (parsed.origin !== currentOrigin) {
                    return;
                }

                var score = getScore(parsed.path, parsed.search, currentPath, currentSearch);
                if (score > bestScore) {
                    bestScore = score;
                    bestElement = this;
                }
            });

            return bestElement ? $(bestElement) : $();
        }

        function applySidebarActiveState() {
            var $sidebar = $('.js-navbar-vertical-aside').first();
            if (!$sidebar.length) {
                return;
            }

            var $preActiveLink = $sidebar.find('li.active > a.nav-link[href]').filter(function() {
                return isNavigableHref($(this).attr('href'));
            }).last();

            var $targetLink = $preActiveLink.length ? $preActiveLink : findBestMatch($sidebar);
            openMenuTree($targetLink);
        }

        $(function() {
            if (window.requestAnimationFrame) {
                window.requestAnimationFrame(applySidebarActiveState);
            } else {
                setTimeout(applySidebarActiveState, 0);
            }
        });
    })(jQuery);
</script>
