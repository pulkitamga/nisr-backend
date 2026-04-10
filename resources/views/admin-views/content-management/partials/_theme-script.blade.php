<script>
    $(document).on('click', '.form-system-language-tab', function (e) {
        e.preventDefault();

        var $tab = $(this);
        var lang = $tab.data('language') || (($tab.attr('id') || '').replace('-link', ''));
        var $scope = $tab.closest('.modal-content');

        if (!$scope.length) {
            $scope = $tab.closest('form');
        }

        if (!$scope.length) {
            $scope = $tab.closest('.content');
        }

        $tab.closest('.nav-tabs').find('.form-system-language-tab').removeClass('active');
        $scope.find('.form-system-language-form').addClass('d-none');
        $tab.addClass('active');

        var $target = $scope.find('.form-system-language-form[data-language="' + lang + '"]').first();
        if (!$target.length) {
            $target = $scope.find('.form-system-language-form[id="' + lang + '-form"]').first();
        }

        $target.removeClass('d-none');
    });
</script>
