(function($){
    "use strict";

    $(document).ready(function () {
        $(document).bind('initFinished.ipContentManagement', function () {
            $('.ipActionWidgetButton')
                .bind('dragstart', function (event, ui) {
                    $('.ipBlock > .ipbExampleContent').each(function () {
                        var $ipExampleContent = $(this);
                        var $block = $ipExampleContent.parent();

                        if ($block.css('min-height')) {
                            // save block min-height in order to restore it
                            $block.data('ipMinHeight', $block.css('min-height'));
                        }

                        $block.css('min-height', $block.height());

                        $ipExampleContent.fadeOut('slow');
                    });
                })
                .bind('dragstop', function (event, ui) {
                    $('.ipBlock > .ipbExampleContent').each(function () {
                        var $ipExampleContent = $(this);
                        var $block = $ipExampleContent.parent();
                        if ($block.children('.ipAdminWidgetPlaceholder').length) {
                            $ipExampleContent.remove();
                        } else {
                            $ipExampleContent.fadeIn('fast');
                        }

                        if (!$block.data('ipMinHeight')) { // block had no min-height before
                            $block.css('min-height', '');
                        } else {
                            $block.css('min-height', $block.data('ipMinHeight'));
                            $block.removeData('ipMinHeight');
                        }
                    });
                });
        });
    });

})(ip.jQuery);
