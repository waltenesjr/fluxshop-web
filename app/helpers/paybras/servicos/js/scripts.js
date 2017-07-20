jQuery(document).ready(
    function()
    {
    //-----------------------------------------------------------------------------------//
    // Criação de Método de Validação de CPF para jquery validate -----------------------//
    //-----------------------------------------------------------------------------------//
    jQuery.validator.addMethod("verificaCPF", function(value, element) {
      var cpf = value.replace(/[^\d]+/g,'');
      if(cpf == '') return true;
      while(cpf.length < 11) cpf = "0"+ cpf;
      var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
      var a = [];
      var b = new Number;
      var c = 11;
      for (i=0; i<11; i++){
        a[i] = cpf.charAt(i);
        if (i < 9) b += (a[i] * --c);
      }
      if ((x = b % 11) < 2) { a[9] = 0 } else { a[9] = 11-x }

      b = 0;
      c = 11;
      
      for (y=0; y<10; y++) b += (a[y] * c--);
      if ((x = b % 11) < 2) { a[10] = 0; } else { a[10] = 11-x; }
      if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg)) return false;
      return true;
    }, "CPF inv&aacute;lido."); // Mensagem padrão

    jQuery.validator.addMethod("verificaCartao", function(value, element) {
      var cardNo = value.replace(/[^\d]+/g,'');
      var checkdigit = true;
      var cardexp = /^[0-9]{13,19}$/;

      if(cardNo == '0000000000001'){
        $("#bandeira_simulacao").attr("checked",true);
        $(".payOptImg").attr('class', 'payOptImg paybras');
        return true;
      } else {

        // Verifica se tamanho está entre 13 e 19
        if (!cardexp.exec(cardNo)) { return false; }


        // Valida Mod10
        if (checkdigit){
          var checksum = 0;
          var mychar = "";
          var j = 1;

          var calc;
          for (i = cardNo.length - 1; i >= 0; i--) {
            calc = Number(cardNo.charAt(i)) * j;
            if (calc > 9) {
              checksum = checksum + 1;
              calc = calc - 10;
            }
            checksum = checksum + calc;
            if (j ==1) {j = 2} else {j = 1};
          }
          if (checksum % 10 != 0) { return false; } // not mod10
        }

        //Seta Bandeiras
        if ((/^(34|37)/).test(cardNo) && cardNo.length <= 15) {
          $("#bandeira_amex").attr("checked",true);
          $("#codigo_seguranca").attr('minlength', '4');
          $("#codigo_seguranca").attr('maxlength', '4');
          $('#label_card').attr("class","amex");
        } else if ((/^(4)/).test(cardNo) && cardNo.length <= 16) {
          $("#bandeira_visa").attr("checked",true);
          $("#codigo_seguranca").attr('minlength', '3');
          $("#codigo_seguranca").attr('maxlength', '3');
          $('#label_card').attr("class",'visa');
        } else if ((/^(5[1-5])/).test(cardNo) && (cardNo.length <= 19)) {
          $("#bandeira_master").attr("checked",true);
          $("#codigo_seguranca").attr('minlength', '3');
          $("#codigo_seguranca").attr('maxlength', '3');
          $('#label_card').attr("class",'master');
        } else if ((/^(30[0-5]|3[68])/).test(cardNo) && cardNo.length <= 16) {
          $("#bandeira_diners").attr("checked",true);
          $("#codigo_seguranca").attr('minlength', '3');
          $("#codigo_seguranca").attr('maxlength', '3');
          $('#label_card').attr("class",'diners');
        } else if ((/^(636368|504175|438935|451416|636297)/).test(cardNo) && cardNo.length == 16){
          $("#bandeira_elo").attr("checked",true);
          $("#codigo_seguranca").attr('minlength', '3');
          $("#codigo_seguranca").attr('maxlength', '3');
          $('#label_card').attr("class",'elo');
        } else {
          return false;
        }
        return true;
      }
    }, "N&uacute;mero de Cart&atilde;o inv&aacute;lido."); // Mensagem padrão

    $('#formCartao').validate({
      rules: {
        //Campos de Cartão
        cartao_numero: {
          required: true,
          verificaCartao: true
        },
        cartao_portador_cpf: {
          verificaCPF: true,
        },
        cartao_portador_nome: {
          required: true
        },
        cartao_codigo_de_seguranca: {
          required: true
        },
        cartao_validade_ano: {
          required: true
        },
        cartao_portador_data_nascimento: {
          date: true
        },
        cartao_parcelas: {
          required: true
        },
      },
      errorPlacement: function(error, element) {
          if(document.getElementById(element.attr('name')) !== null){
              error.insertAfter('#'+element.attr('name'));
          }
      },
      highlight: function(element) {
          $(element).closest('.control-group').addClass('error');
      },
      unhighlight: function(element) {
          $(element).closest('.control-group').removeClass('error');
          $('label.error[for='+element.name+']').remove();
      },
      success: function(element) {
          $(element).closest('.control-group').removeClass('error');
          $('label.error[for='+element.name+']').remove();
      }
    });

    $('#data').tooltip();
    $(".cpfMask").mask("999.999.999-99",{placeholder:"_"});
    $(".dateMask").mask("99/99/9999",{placeholder:"_"});
    $(".dddMask").mask("(99)",{placeholder:""});
    $(".phoneMask").mask("99999999?9",{placeholder:""});
    $(".cepMask").mask("99999-999",{placeholder:"_"});
    $(".ddMask").mask("99",{placeholder:""});
    $(".cardMask").mask("9999999999999?999999",{placeholder:""});

    $("#cartao_codigo_de_seguranca").popover();

    $('.card').on('click', function() {
        if($("#bandeira_amex").attr("checked")){
            $("#codigo_seguranca").attr('minlength', '4');
            $("#codigo_seguranca").attr('maxlength', '4');
        } else {
            $("#codigo_seguranca").attr('minlength', '3');
            $("#codigo_seguranca").attr('maxlength', '3');
        }

        $('#cartao_numero').focus();
    });

    $('#bandeira_visa').on('click', function(event){
      $('#debt').addClass('hide');
      $('#bill').addClass('hide');
      $('#card').removeClass('hide');
      $('#meio_pagamento').val('cartao');
      $('#label_card').attr("class","visa");
    });

    $('#bandeira_master').on('click', function(event){
      $('#debt').addClass('hide');
      $('#bill').addClass('hide');
      $('#card').removeClass('hide');
      $('#meio_pagamento').val('cartao');
      $('#label_card').attr("class","master");
    });

    $('#bandeira_amex').on('click', function(event){
      $('#debt').addClass('hide');
      $('#bill').addClass('hide');
      $('#card').removeClass('hide');
      $('#meio_pagamento').val('cartao');
      $('#label_card').attr("class","amex");
    });

    $('#bandeira_diners').on('click', function(event){
      $('#debt').addClass('hide');
      $('#bill').addClass('hide');
      $('#card').removeClass('hide');
      $('#meio_pagamento').val('cartao');
      $('#label_card').attr("class","diners");
    });

    $('#bandeira_elo').on('click', function(event){
      $('#debt').addClass('hide');
      $('#bill').addClass('hide');
      $('#card').removeClass('hide');
      $('#meio_pagamento').val('cartao');
      $('#label_card').attr("class","elo");
    });

    $('#bb_radio').on('click', function() {
      $('#card').addClass('hide');
      $('#bill').addClass('hide');
      $('#debt').removeClass('hide');
      $('#meio_pagamento').val('tef_bb');
      $('#label_debt').attr("class","bb");
    });

    $('#boleto_radio').on('click', function(event){
      $('#card').addClass('hide');
      $('#debt').addClass('hide');
      $('#bill').removeClass('hide');
      $('#meio_pagamento').val('boleto');
      $('#label_boleto').attr("class","boleto");
    });

    $('.popup').on('click', function() {
      window.open($(this).attr('rel'), '_blank', "height=600,width=960");
    });
  });

  //-----------------------------------------------------------------------------------//
  // Traduções para Plugin de Validação JQuery ----------------------------------------//
  //-----------------------------------------------------------------------------------//

  jQuery.extend(jQuery.validator.messages, {
    required: "Campo Obrigat&oacute;rio.",
    remote: "Por favor, corrija este campo.",
    email: "Por favor, forne&ccedil;a um endere&ccedil;o eletr&ocirc;nico v&aacute;lido.",
    url: "Por favor, forne&ccedil;a uma URL v&aacute;lida.",
    date: "Data inv&aacute;lida.",
    dateISO: "Por favor, forne&ccedil;a uma data v&aacute;lida (ISO).",
    number: "Por favor, forne&ccedil;a um n&uacute;mero v&aacute;lido.",
    digits: "Por favor, forne&ccedil;a somente d&iacute;gitos.",
    creditcard: "Por favor, forne&ccedil;a um cart&atilde;o de cr&eacute;dito v&aacute;lido.",
    equalTo: "Por favor, forne&ccedil;a o mesmo valor novamente.",
    accept: "Por favor, forne&ccedil;a um valor com uma extens&atilde;o v&aacute;lida.",
    maxlength: jQuery.validator.format("Por favor, forne&ccedil;a n&atilde;o mais que {0} caracteres."),
    minlength: jQuery.validator.format("Por favor, forne&ccedil;a ao menos {0} caracteres."),
    rangelength: jQuery.validator.format("Por favor, forne&ccedil;a um valor entre {0} e {1} caracteres de comprimento."),
    range: jQuery.validator.format("Por favor, forne&ccedil;a um valor entre {0} e {1}."),
    max: jQuery.validator.format("Por favor, forne&ccedil;a um valor menor ou igual a {0}."),
    min: jQuery.validator.format("Por favor, forne&ccedil;a um valor maior ou igual a {0}.")
});

function executaMascara(objeto,funcao)
{
  v_obj=objeto
  v_fun=funcao
  setTimeout("executaObjetos()",1)
}

//Método que Executa os objetos
function executaObjetos()
{
  v_obj.value=v_fun(v_obj.value)
}

//Método de Substituição de Valor
function maskValor(v)
{
  v=v.replace(/\D/g,"");
  return v.replace(/(\d{1})(\d{1,2})$/,"$1.$2");
}