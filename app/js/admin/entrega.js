$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    $('.price').mask('000.000.000.000.000,00', {reverse: true});
    $('#btn-add').live('click',function(){
        $('#f-item').submit();
    })
    //remover item
    $('.remove').live('click',function(e){
        e.preventDefault();
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/entrega/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })      

    if ( !$.browser.msie ) {
        $('.hidden').hide();
    }
    
    $('#entrega_cep').live('keyup',function(e){
        if (e.shiftKey || e.ctrlKey || e.altKey) { 
            e.preventDefault();         
        } else { 
            var n = e.keyCode; 
            if (!((n == 8)              
                || (n == 46)             
                //|| (n >= 35 && n <= 40)
                || (n >= 48 && n <= 57)  
                || (n >= 96 && n <= 105))
            ) { 
                e.preventDefault();     
                return false;
            } 
        }     
        var cep = $.trim($('#entrega_cep').val()).replace('_','');
    }) 
    $('#entrega_cep').live('change',function(){
        buscaCep();
    })
    $('#getcep').live('change',function(){
        getCep();        
    })
    $('#showSearchCep').live('click',function(e){
        e.preventDefault();
        $('.accept-opacity').addClass('opaco');
        $('#showSearch').show();
        $('#getcep').focus();
    })
    $('#btn-cep-search-cancel').live('click',function(e){
        e.preventDefault();
        $('.accept-opacity').removeClass('opaco');
        $('#btn-cep-search').removeAttr('disabled');
        $('#showSearch').hide();
        $('#entrega_cep').focus();
        $('#getcep').val('')
        
    })
    $('#btn-cep-search').live('click',function(e){
        e.preventDefault();
        getCep();
        $(this).attr('disabled','disabled');
    })
})
function buscaCep(){    
    var cep = $.trim($('#entrega_cep').val()).replace('_','');
    if(cep.length >= 9){
        var baseUri = $('base').attr('href').replace('/app/','');
        var url = baseUri+'/cep/getcep/';
        $.post(url,{
            cep:cep
        },
        function (data) {
            if(data != -1){
                data = $.parseJSON(data);
                data = data.rs[0];
                $('#entrega_bairro').val(data.bairro);
                $('#entrega_cidade').val(data.cidade);
                $('#entrega_uf').val(data.uf);
                $('#entrega_cep').removeClass('invalid');
                $('.hidden').fadeIn(500);
                $('#entrega_valor').focus();
            }
            else{
                $('#entrega_cep').val('');    
                $('#entrega_cep').addClass('invalid');    
                $('#entrega_cep').focus();  
                $('.hidden').fadeOut();
            }
        })             
    }        
}   
function getCep(){
    var endereco = $.trim($('#getcep').val()).split(" ").join('-');
    var baseUri = $('base').attr('href').replace('/app/','');
    var url = baseUri+'/cep/getend/';   
    $.post(url,{
        endereco:endereco
    },
    function (data) {
        if(data != -1){
            data = $.parseJSON(data);
            data = data.rs[0];
            $('#btn-cep-search').removeAttr('disabled');
            $('#showSearch').fadeOut();
            $('.accept-opacity').removeClass('opaco');
            $('#entrega_cep').val(data.cep);
            $('#entrega_bairro').val(data.bairro);
            $('#entrega_cidade').val(data.cidade);
            $('#entrega_uf').val(data.uf);
            $('#getcep').removeClass('invalid');
            $('#entrega_valor').focus();
        }else{
            $('#getcep').addClass('invalid').focus();
            $('#btn-cep-search').removeAttr('disabled');
            $('#getcep').val('');
        }
    })             
}   
 
