jQuery(document).ready(function($) {
    $('#i18n-add-language-switcher').on('click', function(e) {
        e.preventDefault();
        var url = '#i18n-switcher';
        var label = '🌐 ' + i18nMenus.languageText;

        if (wpNavMenu && typeof wpNavMenu.addLinkToMenu === 'function') {
            wpNavMenu.addLinkToMenu(url, label, wpNavMenu.addMenuItemToBottom);
        } else {
            var newItem = '<li><label class="menu-item-title">' +
                '<input type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-url]" value="' + url + '"> ' +
                label + '</label></li>';
            $('#menu-to-edit').append(newItem);
        }
        return false;
    });
});