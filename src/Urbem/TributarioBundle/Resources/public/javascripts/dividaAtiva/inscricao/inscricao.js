var desabilitarProcessoAssunto = function () {
    factoryObjeto('INPUT', 'processoAssunto').prop('disabled', true);
    factoryObjeto('INPUT', 'processoAssunto').empty().append("<option value=\"\">Selecione</option>").select2("val", "").val('').trigger("change");
};

var desabilitarProcesso = function () {
    factoryObjeto('INPUT', 'processo').prop('disabled', true);
    factoryObjeto('INPUT', 'processo').empty().append("<option value=\"\">Selecione</option>").select2("val", "").val('').trigger("change");
};

jQuery(function() {
    factoryObjeto('WRAP_FORM', CONFIG_INSCRICAO.get('EMITIR_DOCUMENTO')).css({'width': '30%'});
    factoryObjeto('INPUT', "processoClassificacao").on("change", function() {
        if (jQuery(this).val()) {
            desabilitarProcessoAssunto();
            desabilitarProcesso();
            ajaxSelect(CONFIG_INSCRICAO.get('URL_ASSUNTO') + jQuery(this).val(), factoryObjeto('INPUT', 'processoAssunto'), null);
        }
    });

    factoryObjeto('INPUT', "processoAssunto").on("change", function() {
        if (jQuery(this).val()) {
            desabilitarProcesso();
            ajaxSelect(CONFIG_INSCRICAO.get('URL_PROCESSO') + jQuery(this).val(), factoryObjeto('INPUT', 'processo'), null);
        }
    });

    factoryObjeto('INPUT', "processo").on("change", function() {
        if (jQuery(this).val()) {
            factoryObjeto('INPUT', 'hiddenProcesso').val(jQuery(this).val());
        }
    });

    jQuery(document).on('submit', 'form', function(e) {
        desabilitarProcessoAssunto();
        desabilitarProcesso();
    });
});