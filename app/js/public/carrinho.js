var baseUri = $('base').attr('href').replace('/app/','');   
$(function(){    
    var total_frete = 0;
    $('#frete_cep').mask('99999-999');
    $('#endTab').tab();
    $('#endTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    })           
    //adiciona +1 no carrinho
    $('.cart-add').live('click',function(){
        var id = $(this).attr('name');
        var elm = id + ' .input_qtde';
        var url = baseUri+'/carrinho/incrementa/'+id+'/';
        
        $.post(url,{},function(data){
            data = $.parseJSON(data);
            if(data.estoque >= 1){
                $('#'+id + ' .qtde').val(data.qtde);
                $('#'+id + ' .input_qtde').text(data.qtde);
                $('#'+id + ' .sp_total').text(data.total);  
                $('#'+id + ' .sp_total').effect('highlight',2000);  
                $('#total_compra').text('R$ ' +data.total_compra);
                $('#total_compra').effect('highlight',2000);  
                if(data.estoque < 5 && data.estoque >= 1){                    
                    addPop(elm,'Limite no Estoque','Restam apenas '+ ( data.estoque - 1 ) +' itens em nosso estoque!','top');    
                    if(( data.estoque - 1 ) == 0 ){
                        addPop(elm,'Limite no Estoque','Seu pedido atingiu o número máximo deste item em nosso estoque!','top');
                    }
                }
            }else{
                addPop(elm,'Limite no Estoque','Seu pedido atingiu o número máximo deste item em nosso estoque!','top');
            }
        })
        $('#btn-frete-calculo').click();
    })   
    
    //remove -1 no carrinho
    $('.cart-remove').live('click',function(){
        var id = $(this).attr('name');
        var url = baseUri+'/carrinho/decrementa/'+id+'/';
        $.post(url,{},function(data){
            data = $.parseJSON(data);            
            if(data.itens <= 0){
                limparCarrinho();
            }                        
            if(data.qtde == 0){
                if ($.browser.msie) {
                    $('#'+id + ' .qtde').val(data.qtde);
                    $('#'+id + ' .input_qtde').text(data.qtde);
                    $('#'+id + ' .sp_total').text(data.total);  
                    $('#'+id + ' .sp_total').effect('highlight',2000);  
                    $('#total_compra').text('R$ ' +data.total_compra);
                    $('#total_compra').effect('highlight',2000);  
                    $('#'+id).remove(); 
                    return false;                    
                }else{
                    $('#'+id).fadeOut(500,function(){
                        $('#'+id).remove();  
                    });  
                }
            }else{
                $('#'+id + ' .qtde').val(data.qtde);
                $('#'+id + ' .input_qtde').text(data.qtde);
                $('#'+id + ' .sp_total').text(data.total);  
                $('#'+id + ' .sp_total').effect('highlight',2000);  
            }
            $('#btn-frete-calculo').click();
            $('#total_compra').text('R$ '+ data.total_compra);
            $('#total_compra').effect('highlight',2000);  
        })
    })
    //button remove item
    $('.btn-cart-remove').live('click',function(){
        var id = $(this).attr('id');
        var url = baseUri+'/carrinho/remove/'+id+'/';
        window.location = url;
    })
    //limpa carrinho + refresh
    limparCarrinho = function(){
        var url = baseUri+'/carrinho/clear/retorna/';
        window.location = url;
    }
    
    $('.btn-update-frete').live('click',function(){
        var prazo = $(this).attr('p');
        var valor = $(this).attr('v');
        freteReload(valor)
    })
    
    //calculo frete
    $('#btn-frete-calculo').live('click',function(e){
        var $btncalc = $(this);
        e.stopPropagation();
        e.preventDefault();
        var cep = $.trim($('#frete_cep').val());
        if(cep.length <= 8){
            return false;
        }
        freteReload(0);//reset valor frete
        $('#frete_cep').removeClass('invalid');
        $btncalc.button('loading');
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
	           $('#btn-frete-calculo').button('reset');
            }
        })   
    })
})


function freteCorreio(cep,datacep) {
    
    var prog_bar = '<center><img src="images/layout/square_loader.gif" /><br/>Aguarde, calculando frete nos correios...</center>';
    $('#frete_result_pac').html(prog_bar);    
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
                        $('#frete_result_pac').html('<p class="alert alert-error">Serviço dos Correios indisponível</p>');
                    }else{
                        $('#frete_result_pac').html(data);
                    }
                })
            }else{
                $('#frete_result_pac').html('<b>Frete Grátis</b>');
            }
            $('#btn-frete-calculo').button('reset');
        })            
    }
}

function freteReload(v1){  
    var url = baseUri+'/carrinho/nFormata/';
    $.post(url,{
        v1:v1
    },function(data){
        if(v1 >= 1){
            if(logged && logged == true){
                $('.btn-next').show();
            }else{
                $('.btn-login').show();   
            }
        }
        else{
            $('.btn-next').hide();       
        }        
        $('#total_compra').html('R$ ' +data);
        $('#totalCompra').html('R$ ' +data);
    })    
}

function addPop(elm,title,msg,pos) {
    var content = msg;
    $('#'+elm).popover({
        placement:pos,
        title:title,
        html: true, 
        content:content
    });
    var popover = $('#'+elm).data('popover');
    popover.options.content = content;
    $('#'+elm).popover('show');     
}

