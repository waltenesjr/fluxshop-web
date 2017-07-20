<!DOCTYPE html>
<html>
    <head>
        <title>Deixe uma mensagem </title>
        <link rel="shortcut icon" href="${webimroot}/images/favicon.ico" type="image/x-icon"/>
        <link rel="stylesheet" type="text/css" href="${tplroot}/bootstrap/css/bootstrap.css" />
        <script src="${tplroot}/jquery-1.8.3.min.js" type="text/javascript"></script>
        <script src="${tplroot}/bootstrap/js/bootstrap.js" type="text/javascript"></script>
        <script src="${tplroot}/jquery.scrollto.js" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="${tplroot}/chat.css" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <script>
            $('.formcont').live('click',function(){
                setTimeout(function(){
                var baseUri = window.location.href.split("/atd/")
                window.parent.location = baseUri[0] + '/pagina/contato/'
                },100)
            })
        </script>
    </head>
    <body style="background:#f8f8f8;">
        <div id="whitebg" class="span7" style="margin-top: 30px">
            <div style="margin-top: 10px;">
                <div>
                    <div class="span2">
                        <img src="${tplroot}/atd_off.gif" />
                    </div>
                    <div class="span4 alert alert-info">
                        <!-- ${msg:leavemessage.descr}-->
                        Desculpe, o operador não está on-line no momento. <br/>
                        Por favor, tente novamente mais tarde ou preencha o formulário abaixo e aguarde nosso contato.  <br/>

                    </div>
                </div>  
                <br />      <br />      <br />
                <p style="text-align: center; margin-top: 100px;">
                    <button class="btn formcont btn-success"><b class="icon icon-envelope icon-white"></b> Clique aqui para preencher o formulário de contato</button>
                </p>
            </div>
        </div>
        <script type="text/javascript">
        $(function(){
            $('.btn-danger').live('click',function(){
                parent.jQuery.fancybox.close()
            });
        })
        </script>        
    </body>
</html>
