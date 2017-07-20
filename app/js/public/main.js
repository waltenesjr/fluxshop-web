//url base para acoes js
var baseUri = $('base').attr('href').replace('/app/','');  
//carregar mais itens na home
var pageb = 2;
//url que guarda a query do carregar mais
var route;
//maior / menor preco 
var preco_max = 0;
var preco_min = 0; 

$(function(){
    //todas as categorias do menu
    $('#lista-categorias').hide();
    $('#show-lista-categorias').hover(function(){
        $('#lista-categorias').fadeIn(100);
    })    
    $('#lista-categorias').on("mouseleave",function(){
        $('#lista-categorias').fadeOut(100);
    })    
    if($(document).height() >= 1300){
        //navbar bootstrap exibe o menu no hover 
        jQuery('ul.nav li.dropdown').hover(function() {
            jQuery(this).find('.dropdown-menu').stop(true, true).delay(120).fadeIn();
            $('#lista-categorias').hide();
        }, function() {
            jQuery(this).find('.dropdown-menu').stop(true, true).delay(120).fadeOut();
        });
    }  	
    window.onload = function(){              
        //tootips
        $('.tips-top').tooltip({
            placement:'top'
        });    
        $('.tips-left').tooltip({
            placement:'left'
        });    
        $('.tips-bottom').tooltip({
            placement:'bottom'
        });    
        $('.tips-right').tooltip({
            placement:'right'
        });  
        //topo menu float ao rolar a pagina
        if($(document).height() >= 1200){
            var elm = $('#top'),  pos = elm.offset();	
            var elmH = $('#top-menu');	
            $(window).scroll(function(){        
                if($(this).scrollTop() >= pos.top+$('#top').height()){
                    elmH.removeClass('default').addClass('fixed');
                    $('.cart-hide').show();              
                } else {
                    elmH.removeClass('fixed').addClass('default');
                    $('.cart-hide').hide();
                }
            });
        }  
    }
    //carregar mais itens na home
    var page = 2;
    $('#carregar-mais-home').live('click',function(){     
        $(this).hide();
        $('.box-all').append('<div id="load_page_add" class="span8 center"><img src="images/layout/loadmore.gif" /></div>');
        var url = baseUri + '/index/loadMore/page/'+page+'/';
        $.post(url,{},function(data){
            if(data != -1){
                $('.box-all').append(data);
                $('html, body').animate({
                    scrollTop: $('#load_page_add').offset().top - 60
                }, 1300);  
                $('#load_page_add').remove();
                $('.up2top').fadeIn();
                page++;
            }else{
                $('#load_page_add').remove();
                $('#carregar-mais-home')
                $('#carregar-mais')
                .attr('disable','disable')
                .hide() 
            }
        })
    })
    //carregar mais itens 
    $('#carregar-mais').live('click',function(){     
        $(this).hide();
        $('.box-all').append('<div id="load_page_add" class="span8 center"><img src="images/layout/loadmore.gif" /></div>');
        if(route){
            var url = route + '/page/'+pageb+'/';    
        }else{
            var url = baseUri + '/index/loadMore/page/'+pageb+'/';
            route = baseUri;
        }
        $.post(url,{},function(data){
            if(data != -1){
                $('.box-all').append(data);
                $('html, body').animate({
                    scrollTop: $('#load_page_add').offset().top - 60
                }, 1300);
                $('#load_page_add').remove();
                $('.up2top').fadeIn();
                pageb++;
            }else{
                $('#load_page_add').remove();
                $('#carregar-mais')
                .attr('disable','disable')
                .hide()
            }
        })
    })
    //ordenar resultados
    $('.sort-list').live('change',function(){
        pageb = 2;
        if($(this).val() != '0'){
            $('#carregar-mais').hide();
            $('.box-all').html('');
            $('.box-all').append('<div id="load_page_add" class="span8 center"><img src="images/layout/loadmore.gif" /></div>');
            var url = baseUri+'/index/ordenar/'+ $(this).val();
            route = url;
            $.post(url,{},function(data){
                if(data != -1){
                    $('.box-all').append(data);
                    $('#load_page_add').remove();
                }else{
                    $('#load_page_add').remove();
                    $('#carregar-mais').hide()
                }
            })       
        } 
        return false;
    })   
    //busca
    $('#busca').submit(function(){
        if( $('#busca').val() == ""){
            $('#busca').focus();
            return false;
        }
    })
    //recuperar senha
    $('.btn-repass').live('click',function(){
        $('.message_login').html('');
        $('#nav-login').hide();
        $('#nav-login-repass').show();
    })
    
    //correr para o topo
    $('.up2top').live('click',function(){
        $('html, body').animate({
            scrollTop: $('#top').offset().top - 60
        }, 1300)          
    })
});
//seta range valor apos usar filtros
function setRangePreco(min,max) {
    if(min <= preco_min){
        preco_min = min;
    }
    preco_min = min;
    if(max >= preco_max){
        preco_max = max;
    }
    var n_input = '<input type="text" class="" value="" ' 
    n_input += ' data-slider-min="'+(preco_min - 5)+'" ';
    n_input += ' data-slider-max="'+(preco_max + 50)+'" ';
    n_input += ' data-slider-step="30" ';
    n_input += ' data-slider-value="['+(preco_min - 5)+','+(preco_max + 50)+']" ';
    n_input += ' id="range-preco">';
    $('#p-range-preco').html(n_input);
    $('#range-preco').slider().on('slide', function(ev){
        v_min = ev.value[0];
        v_max = ev.value[1];
        $('.item-box').each(function(){
            var preco = $(this).attr('preco');
            var id = $(this).attr('id');
            if(  preco >= v_min && preco <= v_max){
                $(this).fadeIn();
            }else{
                $(this).fadeOut()
            }
        })
    });            
}
//oculta botao exibir mais
function hideShowBtnMore(hs) {
    if(hs == 1)
        $('.btn-load-more').hide();
    else
        $('.btn-load-more').show();
}
//paginacao home via get/post
function initHomeItem() {
    var url = baseUri + '/index/loadMore/page/2/';
    $.post(url,{},function(data){
        if(data != -1){
            $('.box-all').append(data);
        }
    })    
}
//menu horizontal, user logado , checa no footer.html
function replaceMenu(logged) {
    
    //fix IE
    var ua = window.navigator.userAgent;
    var msie = ua.indexOf("MSIE ");
    if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
        $('#top-menu .navbar').addClass('pull-left');
    }
        
    $('.navbar').show();
    if(logged && logged == true){
        $('.nologged').hide();
        $('.logged').show();
    }
    else{
        $('.logged').hide();
        $('.nologged').show();
    }     
}
//set menu categoria ativo / setado no footer.html
function setActiveMenu(catAct,subAct){
    if(catAct != ""){
        $('.'+catAct).addClass('active');
    }    
}
