var edited_i18nFormElement= '';

function changeI18nValues(input_id){
    edited_i18nFormElement = input_id;
    window.open(
        'i18n/getSelector.php?id='+input_id,
        'sh_i18n',
        config='height=400, width=450, toolbar=no, menubar=no'
    );
}

function submitI18n(editor,html){
    $(editor).innerHTML = html;
}
