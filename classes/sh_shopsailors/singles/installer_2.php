<div class="step">Step 2 : Database and Domain settings</div>
<?php
if(!empty($error)){
    echo $error;
}
?>
<hr />
<fieldset>
    <legend>
        <input type="radio" name="install" value="both" id="install_both" checked="checked" class="f_input"/>
        <label for="install_both">
            Install a master server (which manages the users datas) and a site.
        </label>
    </legend>
    <div class="fieldset_content" id="f_install_both">
        <div class="subtitle">Master server's datas</div>
        Domain name :
        <input name="both_ms[domain]"/><br />
        <div class="notification">
            Must be DNS + Apache rooted to this site. Ex : ms123456.mydomain.com
        </div>
        Database server : <input name="both_ms[server]" value="localhost"/><br />
        Database admin username : <input name="both_ms[user]"/> * <br />
        Database admin password : <input name="both_ms[password]" type="password"/> * <br />
        Database name : <input name="both_ms[db]"/><br />
        Tables prefix : <input name="both_ms[prefix]"/>

        <hr />
        <div class="subtitle">Site's datas</div>
        Domain name :
        <input name="both_site[domain]"/><br />
        <div class="notification">
            Must be DNS + Apache rooted to this site. Ex : www.mydomain.com
        </div>
        Database server : <input name="both_site[server]" value="localhost"/><br />
        Database admin username : <input name="both_site[user]"/> * <br />
        Database admin password : <input name="both_site[password]" type="password"/> * <br />
        Database name : <input name="both_site[db]"/><br />
        Tables prefix : <input name="both_site[prefix]"/>
    </div>
</fieldset>

<fieldset>
    <legend>
        <input type="radio" name="install" value="masterServer_only" id="install_masterServer" class="f_input"/>
        <label for="install_masterServer">
            Install a master server only (which manages the users datas).
        </label>
    </legend>
    <div class="fieldset_content" id="f_install_masterServer">
        Domain name :
        <input name="ms_ms[domain]"/><br />
        <div class="notification">
            Must be DNS + Apache rooted to this site. Ex : ms123456.mydomain.com
        </div>
        Database server : <input name="ms_ms[server]" value="localhost"/><br />
        Database admin username : <input name="ms_ms[user]"/> * <br />
        Database admin password : <input name="ms_ms[password]" type="password"/> * <br />
        Database name : <input name="ms_ms[db]"/><br />
        Tables prefix : <input name="ms_ms[prefix]"/>
    </div>
</fieldset>

<fieldset>
    <legend>
        <input type="radio" name="install" value="site_only" id="install_site" class="f_input"/>
        <label for="install_site">
            Install a site only (need an external master server)
        </label>
    </legend>
    <div class="fieldset_content" id="f_install_site">
        Domain name :
        <input name="site_site[domain]"/><br />
        <div class="notification">
            Must be DNS + Apache rooted to this site. Ex : www.mydomain.com
        </div>
        Database server : <input name="site_site[server]" value="localhost"/><br />
        Database admin username : <input name="site_site[user]"/> * <br />
        Database admin password : <input name="site_site[password]" type="password"/> * <br />
        Database name : <input name="site_site[db]"/><br />
        Tables prefix : <input name="site_site[prefix]"/>
    </div>
</fieldset>

<div class="notification">
    * : These datas won't be used again (nor stored) after the installation finishes.<br />
    The user should have GRANT OPTION, in order to create the accounts, and if they don't already exist,
    CREATE DATABASE, to create the databases.
</div>

<script type="text/javascript">
$$('.fieldset_content').each(function(el){
    el.style.display = 'none';
});
$$('.f_input').each(function(el){
    if(el.checked){
        $('f_'+el.id).style.display = 'block';
    }
    Event.observe(el,'change',function(){
        $$('.fieldset_content').each(function(el2){
            el2.style.display = 'none';
        });
        $('f_'+el.id).style.display = 'block';
    });
});
</script>
<input type="submit" name="Next" value="next"/>