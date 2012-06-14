<div class="step">Step 2 : Installation</div>
<?php
if(!empty($error)){
    echo $error;
}
?>
Email : <input name="mail"/> 
<div class="notification">
    After the installation, you will have to create an account.<br />
    You will have to use this email to get the rights to manage this installation.<br />
    This email will not be used for anything else.
</div>

<hr />
<fieldset>
    <legend>
        <input type="radio" name="install" value="both" id="install_both" checked="checked" class="f_input"/>
        <label for="install_both">
            Install a master server (which manages the users datas) and a site.
        </label>
    </legend>
    <div class="fieldset_content" id="f_install_both">
        <table>         
        <tr><td colspan="2">
        <div class="subtitle">Master server's datas</div> 
        </td></tr> 
        <tr><td>Domain name :</td><td><input name="both_ms[domain]"/></td></tr>  
        <tr><td colspan="2">
        <div class="notification">
            Must be DNS + Apache rooted to this site and folder.<br />Ex : ms123456.mydomain.com
        </div> 
        </td></tr> 
        <tr><td>Database server : </td><td><input name="both_ms[server]" value="localhost"/></td></tr> 
        <tr><td>Database admin username * : </td><td><input name="both_ms[user]"/></td></tr>        
        <tr><td>Database admin password * : </td><td><input name="both_ms[password]" type="password"/></td></tr> 
        <tr><td>Database name : </td><td><input name="both_ms[db]"/></td></tr> 
        <tr><td>Tables prefix : </td><td><input name="both_ms[prefix]"/> <span class="notification">(optionnal)</span></td></tr> 
               
        <tr><td colspan="2">
        <hr /> 
        </td></tr> 
           
        <tr><td colspan="2">
        <div class="subtitle">Site's datas</div> 
        </td></tr> 
        <tr><td>Domain name :</td><td><input name="both_site[domain]"/></td></tr>  
        <tr><td colspan="2">
        <div class="notification">
            Must be DNS + Apache rooted to this site and folder.<br />Ex : www.mydomain.com
        </div>
        </td></tr> 
        <tr><td>Database server : </td><td><input name="both_site[server]" value="localhost"/></td></tr> 
        <tr><td>Database admin username * : </td><td><input name="both_site[user]"/></td></tr> 
        <tr><td>Database admin password * : </td><td><input name="both_site[password]" type="password"/></td></tr> 
        <tr><td>Database name : </td><td><input name="both_site[db]"/></td></tr> 
        <tr><td>Tables prefix : </td><td><input name="both_site[prefix]"/> <span class="notification">(optionnal)</span></td></tr> 
        </table>
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
        <table>         
        <tr><td colspan="2">
        <div class="subtitle">Master server's datas</div> 
        </td></tr> 
        <tr><td>Domain name :</td><td><input name="ms_ms[domain]"/></td></tr>  
        <tr><td colspan="2">
        <div class="notification">
            Must be DNS + Apache rooted to this site and folder.<br />Ex : ms123456.mydomain.com
        </div> 
        </td></tr> 
        <tr><td>Database server : </td><td><input name="ms_ms[server]" value="localhost"/></td></tr> 
        <tr><td>Database admin username * : </td><td><input name="ms_ms[user]"/></td></tr>        
        <tr><td>Database admin password * : </td><td><input name="ms_ms[password]" type="password"/></td></tr> 
        <tr><td>Database name : </td><td><input name="ms_ms[db]"/></td></tr> 
        <tr><td>Tables prefix : </td><td><input name="ms_ms[prefix]"/> <span class="notification">(optionnal)</span></td></tr> 
        </table>
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
        <table>      
        <tr><td colspan="2">
        <div class="subtitle">Site's datas</div> 
        </td></tr> 
        <tr><td>Domain name :</td><td><input name="site_site[domain]"/></td></tr>  
        <tr><td colspan="2">
        <div class="notification">
            Must be DNS + Apache rooted to this site and folder.<br />Ex : www.mydomain.com
        </div>
        </td></tr> 
        <tr><td>Database server : </td><td><input name="site_site[server]" value="localhost"/></td></tr> 
        <tr><td>Database admin username * : </td><td><input name="site_site[user]"/></td></tr> 
        <tr><td>Database admin password * : </td><td><input name="site_site[password]" type="password"/></td></tr> 
        <tr><td>Database name : </td><td><input name="site_site[db]"/></td></tr> 
        <tr><td>Tables prefix : </td><td><input name="site_site[prefix]"/> <span class="notification">(optionnal)</span></td></tr> 
        </table>
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