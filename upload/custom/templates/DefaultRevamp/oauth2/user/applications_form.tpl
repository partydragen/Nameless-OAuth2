{include file='header.tpl'}
{include file='navbar.tpl'}

<h2 class="ui header">
    {$TITLE}
</h2>

{if isset($SUCCESS)}
    <div class="ui success icon message">
        <i class="check icon"></i>
        <div class="content">
            {$SUCCESS}
        </div>
    </div>
{/if}

{if isset($ERRORS)}
    <div class="ui negative icon message">
        <i class="x icon"></i>
        <div class="content">
            {foreach from=$ERRORS item=error}
                {$error}<br />
            {/foreach}
        </div>
    </div>
{/if}

<div class="ui stackable grid" id="user">
    <div class="ui centered row">
        <div class="ui six wide tablet four wide computer column">
            {include file='user/navigation.tpl'}
        </div>
        <div class="ui ten wide tablet twelve wide computer column">
            <div class="ui segment">
                <h3 class="ui header">{$EDITING_APPLICATION}</h3>

                <form class="ui form" action="" method="post" id="form-new-application">
                    <div class="field">
                        <label for="inputClientID">{$CLIENT_ID}</label>
                        <input type="text" name="client_id" id="inputClientID" value="{$CLIENT_ID_VALUE}" readonly>
                    </div>

                    <div class="field">
                        <label for="inputClientSecret">{$CLIENT_SECRET}</label>
                        <input type="text" name="client_secret" id="inputClientSecret" value="{$CLIENT_SECRET_VALUE}" readonly>
                    </div>

                    <div class="ui divider"></div>

                    <div class="field">
                        <label for="inputName">{$APPLICATION_NAME}</label>
                        <input type="text" name="name" id="inputName" placeholder="{$APPLICATION_NAME}" value="{$APPLICATION_NAME_VALUE}">
                    </div>

                    <div class="field">
                        <label for="inputRedirectURI">{$REDIRECT_URI}</label>
                        <input type="text" name="redirect_uri" id="inputRedirectURI" placeholder="{$REDIRECT_URI}" value="{$REDIRECT_URI_VALUE}">
                    </div>

                    <div class="field">
                        <input type="hidden" name="token" value="{$TOKEN}">
                        <input type="submit" class="ui primary button" value="{$SUBMIT}">
                    </div>

                    <div class="ui divider"></div>

                    <div class="field">
                        <label for="inputOAuthURL">{$OAUTH2_URL}</label>
                        <input type="text" name="oauth_url" id="inputOAuthURL" value="{$OAUTH2_URL_VALUE}" readonly>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

{include file='footer.tpl'}