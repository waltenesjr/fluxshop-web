<!DOCTYPE HTML>
<html>
    <head>
        <title>Atendimento Online</title>
        <link rel="shortcut icon" href="${webimroot}/images/favicon.ico" type="image/x-icon">
        <link rel="stylesheet" type="text/css" href="${tplroot}/bootstrap/css/bootstrap.css" />        
        <link rel="stylesheet" type="text/css" href="${tplroot}/chat.css" />
        <script src="${webimroot}/js/jquery-1.3.2.min.js" type="text/javascript"></script>
        <script type="text/javascript" language="javascript" src="${webimroot}/js/${jsver}/common.js"></script>
        <script type="text/javascript" language="javascript" src="${webimroot}/js/${jsver}/brws.js"></script>
        <script type="text/javascript" language="javascript">
            var threadParams = { servl:"${webimroot}/thread.php",wroot:"${webimroot}",frequency:${page:frequency},${if:user}user:"true",${endif:user}threadid:${page:ct.chatThreadId},token:${page:ct.token},cssfile:"${tplroot}/chat.css",ignorectrl:${page:ignorectrl} };
        </script>
        <script type="text/javascript" language="javascript" src="${webimroot}/js/${jsver}/chat.js"></script>
        <style type="text/css">
            .isound { background: url(${tplroot}/images/buttons/sound.gif) no-repeat; width: 19px; height: 19px; }
            .inosound { background: url(${tplroot}/images/buttons/nosound.gif) no-repeat; width: 19px; height: 19px; }
        </style>
    </head>
    <body style="background:#f8f8f8;">

        <div id="main" class="span7" style="margin-left:35px;margin-top:0px; border:1px solid #f8f8f8">
            ${if:user}	
            <img src="${tplroot}/atd_on.gif" /> <br/><br/>
            ${endif:user}

            <div id="top">

                ${if:agent}
                <div id="cname" class="span3" style="margin-left:0px; margin-right:68px;border:0px solid red;">	
                    ${if:historyParams}
                    ${msg:chat.window.chatting_with}
                    <a href="${page:historyParamsLink}" target="_blank" title="${msg:page.analysis.userhistory.title}" 
                       onClick="this.newWindow = window.open('${page:historyParamsLink}', 'UserHistory', 
                           'toolbar=0,scrollbars=0,location=0,statusbar=1,menubar=0,width=703,height=380,resizable=1');
                           this.newWindow.focus();this.newWindow.opener=window;return false;">
                        ${page:ct.user.name}
                    </a>
                    ${else:historyParams}
                    ${msg:chat.window.chatting_with} <b> ${page:ct.user.name}</b>
                    ${endif:historyParams}
                </div>
                ${endif:agent}

                ${if:user}
                <div id="cname" class="span4" style="margin-left:0px">	
                    <span class="label label-info"><b class="icon-user icon-white"></b> ${page:ct.user.name}</span>
                    <div style="display:none">
                        ${if:canChangeName}						
                        <div id="changename1" style="display:${page:displ1};">						
                            <div class="input-prepend input-append">									 
                                <input id="uname" type="text" value="${page:ct.user.name}" />
                                <a class="btn" href="javascript:void(0)" onClick="return false;" title="${msg:chat.client.changename}">Mudar Nome</a>
                            </div>	
                        </div>						
                        <div id="changename2" style="display:${page:displ2};">
                            <div class="input-prepend input-append">									 
                                <input id="uname" type="text" value="${page:ct.user.name}" />
                                <a class="btn" href="javascript:void(0)" onClick="return false;" title="${msg:chat.client.changename}">Mudar Nome</a>
                            </div>					
                        </div>
                        ${else:canChangeName}
                        <p>${msg:chat.client.name}&nbsp;${page:ct.user.name}</p>
                        ${endif:canChangeName}
                    </div>
                </div>
                ${endif:user}

                <div id="ctrls" class="span2" style="margin-left:61px;text-align: right;margin-top:3px;border:0px solid red;">
                    ${if:user}		
                    <!--
                    <a href="${page:mailLink}&amp;style=${styleid}" target="_blank" title="enviar histÃ³rico por e-mail" 
                       onClick="this.newWindow = window.open('${page:mailLink}&amp;style=${styleid}', 'ForwardMail', 'toolbar=0,scrollbars=0,
                           location=0,statusbar=1,menubar=0,width=603,height=254,resizable=0'); if (this.newWindow != null) 
                           {this.newWindow.focus();this.newWindow.opener=window;}return false;" class="btn btn-mini">
                        <b class="icon-envelope"></b>
                    </a>
                    -->
                    ${endif:user}
                    ${if:agent}
                    ${if:canpost}
                    <!--
                            <a class="btn btn-mini" href="${page:redirectLink}&amp;style=${styleid}" title="redirecionar para outro atendente">
                                    <b class="icon-retweet"></b>
                            </a>		
                    -->
                    ${endif:canpost}
                    ${if:historyParams}

                    <a  class="btn btn-mini" href="${page:historyParamsLink}" target="_blank" title="histórico de visitas" 
                        onClick="this.newWindow = window.open('${page:historyParamsLink}', 'UserHistory', 
                            'toolbar=0,scrollbars=0,location=0,statusbar=1,menubar=0,width=720,height=480,resizable=1');this.newWindow.focus();this.newWindow.opener=window;return false;">
                        <b class="icon-list-alt"></b>
                    </a>			
                    ${endif:historyParams}
                    ${endif:agent}

                    <a id="togglesound" href="javascript:void(0)" onClick="return false;" title="Som On/Off" class="btn btn-mini">
                        <b class="icon-volume-off"></b>
                    </a>		
                    <a id="refresh" href="javascript:void(0)" onClick="return false;" title="atualizar chat" class="btn btn-mini">
                        <b class="icon-refresh"></b>
                    </a>		
                    <a class="closethread btn btn-mini" href="javascript:javascript:parent.jQuery.fancybox.close()" onClick="javascript:parent.jQuery.fancybox.close()" title="sair do chat" >
                        <b class="icon-remove"></b>
                    </a>		
                </div>

            </div>

            <table id="chat" cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td>
                        <div id="engineinfo" style="display:none;"></div>
                        <div id="typingdiv" style="display:none;">${msg:typing.remote}</div>&nbsp;
                    </td>
                </tr>
                <tr>
                    <td valign="top">

                        <iframe id="chatwnd" class="chathistory" style="width:500px; height:220px;border:1px solid #999"
                                src="${if:neediframesrc}${webimroot}/images/blank.html${endif:neediframesrc}" frameborder="0">
                        Sorry, your browser does not support iframes; try a browser that supports W3C standards.
                        </iframe>
                    </td>
                    <td width="100" valign="top">
                        <div id="avatarwnd"></div>
                    </td>
                </tr>
                ${if:canpost}
                <tr>
                    <td valign="top" id="postmessage">
                        ${if:agent}
                        <p>
                            <select id="predefined"  class="dropdown" style="width:502px">
                                <option>Selecione uma mensagem pronta...</option>
                                ${page:predefinedAnswers}
                            </select>
                        </p>
                        ${endif:agent}				
                        <textarea id="msgwnd" class="message" tabindex="0" placeholder="ou digite uma mensagem" style="width:488px; height:50px;resize:none"></textarea>
                        <br />
                        <p class="submit" style="margin-top:5px;">
                            <a id="sndmessagelnk" href="javascript:void(0)" class="btn btn-small btn-success" onClick="return false;" style="color:#fff;">
                                <b class="icon-comment icon-white"></b> Enviar Mensagem</a>				
                        </p>
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td></td>
                </tr>
                ${endif:canpost}
            </table>
        </div>
    </body>
</html>

