<?php
error_reporting( 0 );
class Social extends PHPFrodo
{
    public $social_fb = '';
    public $social_tw = '';
    public $social_gp = '';
    public $social_in;
    public $social_f4;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();

        $this->select()->from( 'social' )->execute();
        if ( $this->result() )
        {
            $this->map( $this->data[0] );
            //plugin social Like Facebook
            if ( $this->social_fb != '' )
            {
                //<h4>Curta Nossa Página no Facebook</h4>
                $plugin = '
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1&appId=375098389245172";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, "script", "facebook-jssdk"));</script>

            <div class="fb-like-box" 
                 data-href="' . $this->social_fb . '"
                 data-width="960"
                 data-show-faces="true"
                 data-header="false"
                 data-stream="false"
                 data-colorscheme="dark"
                 data-show-border="false">
            </div>';
                $this->social_fb = '<div class="mar-top-50"><h4>Curta nossa página no Facebook</h4>' . $plugin . '</div>';
            }
            $this->social_tw = '';
            if ( $this->social_tw != '' )
            {
                // data-tweet-limit="4"
                $plugin = $this->social_tw;
                $plugin = preg_replace( '/data-dnt\=\"true\"/', 'data-dnt="true" data-chrome="nofooter noborders transparent" height="200" ', $plugin );
                $this->social_tw = '<div class="mar-top-50"><h4>Siga-nos no Twitter</h4>' . $plugin . '</div>';
            }
        }
    }
}
/*end file*/
