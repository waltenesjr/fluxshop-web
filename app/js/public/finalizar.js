var baseUri = $('base').attr('href').replace('/app/','');  
$(function(){
    //identificacao login-cadastro
    $('.cad').live('click',function(){
        $(this).each(function(){
            if($(this).attr('checked') == 'checked'){
                if($(this).attr('id') == 'cadastrar'){
                    $('#cliente_password').removeAttr('required');
                    $('#cliente_password').attr('disabled','disabled');
                }else{
                    $('#cliente_password').removeAttr('disabled');
                    $('#cliente_password').attr('required');
                }
            }
        })
    })
    //tabs enderecos
    $('#endTab').tab();
    $('#endTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })  
    $('#endTab a:eq(1)').tab('show');
    $('#endTab a:eq(0)').tab('show');  
    //selecao enderecos /entrega e retirada
    var tipo_entrega;
    $('.end-entrega').live('click',function(){
        $('#frete_result').html('');
        $('html, body').animate({
            scrollTop: $('#btn-finaliza').offset().top
        }, 800)             
        $(this).each(function(){
            tipo_entrega = $(this).attr('tipo');
            tipo_entrega_cep = $(this).attr('cep');
            addr  = $(this).attr('addr');
            addr_id = $(this).val();
            //gravar na sessao PHP
            $('#entrega_selecionada').val(addr_id);
            $('#entrega_selecionada_tipo').val( tipo_entrega)
            $('#entrega_selecionada_id').val(tipo_entrega_cep);
            $('#entrega_selecionada_desc').val(addr);   
            if( tipo_entrega == 1){
                $('#btn-finaliza').attr('disabled','disabled');
                $('#btn-finaliza').html('calculando frete, aguarde...');
                calculaFrete(tipo_entrega_cep);
            }else{
                $('#btn-finaliza').removeAttr('disabled');
                $('#btn-finaliza').html('Prosseguir <i class="icon-chevron-right"></i>');                  
            }
        })
    })    
    
    $('.metodo-pagamento').live('click',function(){
        $('#btn-finaliza').removeAttr('disabled');
        $('#btn-finaliza').html('Prosseguir')
        $('html, body').animate({
            scrollTop: $('#btn-finaliza').offset().top
        }, 800)         
    }) 
    $('#btn-finaliza').live('click',function(){
        $('#btn-finaliza').button('loading');
    })
    
    $('.btn-cupom-valida').live('click',function(){
        var cupom = $.trim($('#cupom').val());
        //if(cupom != ''){
        var url = baseUri + '/cupom/validar/';
        $.post(url,{
            cupom:cupom
        },function(data){
            if(data == -3){
                $('#cupom-msg').removeClass('alert-error').addClass('alert alert-success');
                $('#cupom-msg').html('<b>Cupom Frete Grátis!</b>');      
                setTimeout(function(){
                    window.location.href = window.location.href;
                },1000)                
            }else if(data == -2){
                $('#cupom-msg').removeClass('alert-success').addClass('alert alert-error');
                $('#cupom-msg').html('<b>Validade do Cupom Vencida!</b>');   
                setTimeout(function(){
                    window.location.href = window.location.href;
                },1500)
            }else if(data != -1){
                data = $.parseJSON(data);
                window.location.href = window.location.href;
            }else{
                $('#cupom-msg').removeClass('alert-success').addClass('alert alert-error');
                $('#cupom-msg').html('<b>Cupom Inválido!</b>');
                setTimeout(function(){
                    window.location.href = window.location.href;
                },1500)
            }
        })
    //}        
    })

})
function ocultaRetirada(){
    $("#retirada").hide();
    $("#retirada").remove();
}
function ocultaEntrega(){
    $("#entrega").hide();
    $("#entrega").remove();
}
function ocultaPayPal(){
    $("#paypal").hide();
    $("#paypal").remove();
}
function ocultaPayBras(){
    $("#paybras").hide();
    $("#paybras").remove();
}
function ocultaPagSeguro(){
    $("#pagseguro").hide();
    $("#pagseguro").remove();
}

$('.btn-update-frete').live('click',function(){
    var prazo = $(this).attr('p');
    var valor = $(this).attr('v');
    var tipo = $(this).attr('t');
    freteReload(valor.replace(',','.'),prazo,tipo);
})
    
//calculo frete
function calculaFrete(cep) {
    var url = baseUri+'/cep/getcep/';    
    $.post(url,{
        cep:cep
    },
    function (data) {           
        if(data != -1){
            data = $.parseJSON(data);
            data = data.rs[0];
            var datacep = {};
            if(data.cep_unico == 0){
                datacep = {
                    cep: cep,
                    uf: data.uf,
                    cidade: data.cidade,
                    bairro: data.bairro
                };
            }else{
                datacep = {
                    cep: cep,
                    uf: data.uf,
                    cidade:data.cidade, 
                    bairro: data.bairro
                };   
            }
            freteCorreio(cep,datacep);
        }else{
            $('#frete_result_pac').html(''); 
            var msg = '<p class="font-12">Confirme seu cep e tente novamente.</p>';
            var tit = '<p class="font-12"><b>Cep não encontrado!</b></p>';
            addPop('frete_cep',tit,msg,'bottom');
            $('#frete_cep').addClass('invalid').focus();
        }
    })   
}

function freteCorreio(cep,datacep) {
    var prog_bar = '<center><img src="images/layout/square_loader.gif" /><br/>Aguarde, calculando frete nos correios...</center>';
    $('#frete_result').html(prog_bar);    
    if(cep.length >= 9){
        var url = baseUri+'/carrinho/nCalculo/';
        $.post(url,{},function(data){     
            var rs = $.parseJSON(data);
            if(rs.p != 0){ //calcula frete
                if( rs.p == '-1'){
                    carrinhoVazio();
                }
                if(rs.cf == 'sim'){
                    var url = baseUri+'/frete/correios/';
                }else{
                    //nao calcula valor, somente prazo
                    var url = baseUri+'/frete/correios/no-cf/';
                }          
                $.post(url,{
                    comprimento:rs.c,
                    largura:rs.l,
                    altura:rs.a,
                    peso:rs.p,
                    cep:cep,
                    uf: datacep.uf,
                    cidade:datacep.cidade, 
                    bairro: datacep.bairro
                }
                ,function(data){
                    if(data == '-1'){
                        $('#frete_result').html('<p class="alert alert-error">Serviço dos Correios indisponível</p>');
                    }else{
                    }
                    $('#frete_result').html(data);
                })
            }else{
                $('#frete_result').html('<b>Frete Grátis</b>');
            }
            $('#btn-frete-calculo').button('reset');
        })   
        $('#btn-finaliza').html('Aguardando op&#231;&#227;o de frete');
        $('#modal-frete').modal('show');          
    /*
        $('.btn-update-frete').live('click',function(){
            var prazo = $(this).attr('p');
            var valor = $(this).attr('v');
            var tipo = $(this).attr('t');
            freteReload(valor.replace(',','.'),prazo,tipo);
        })    
        */
    }
}

function freteReload(v1,v2,v3){  
    var url = baseUri+'/carrinho/nFormata/';
    $.post(url,{
        v1:v1,
        v2:v2,
        v3:v3
    },function(data){
         $('#btn-finaliza').html('Prosseguir >>>');			
        $('#btn-finaliza').removeAttr('disabled');        
        $('#modal-frete').modal('hide');   
    /*
        if(v1 >= 1){
        }else{
            window.location = baseUri + '/finalizar/entrega/';
        }
        */
    })    
}

function carrinhoVazio(){
    window.location = baseUri + '/carrinho/';
}