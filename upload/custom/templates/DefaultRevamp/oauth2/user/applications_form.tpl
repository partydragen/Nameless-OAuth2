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
                        <div class="ui action input">
                            <input type="text" name="client_secret" id="inputClientSecret" value="{$CLIENT_SECRET_VALUE}" readonly>
                            <a class="ui button" href="#" data-toggle="modal" data-target="#modal-regen">{$REGEN}</a>
                        </div>
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
                        <input type="hidden" name="action" value="general" />
                        <input type="submit" class="ui primary button" value="{$SUBMIT}">
                    </div>
                </form>
            </div>

            <div class="ui segment">
                <h3 class="ui header">{$OAUTH2_URL_GENERATOR}</h3>
                <form class="ui form" action="" method="post" id="form-url-generator">
                    <div class="field">
                        <label for="inputOAuthURL">{$OAUTH2_URL}</label>
                        <input type="text" name="oauth_url" id="inputOAuthURL" placeholder="{$SELECT_SCOPES_TO_GENERATE}" readonly>
                    </div>

                    <div class="field">
                        <label for="inputOAuthURL">{$SCOPES}</label>
                        <div class="ui grid">
                            {foreach from=$SCOPES_LIST key=scope item=item}
                                <div class="four wide column">
                                    <div class="field">
                                        <div class="ui checkbox">
                                            <input type="checkbox" name="scopes" value="{$scope}">
                                            <label>{$scope}</label>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="ui small modal" id="modal-regen">
    <div class="header">
        {$ARE_YOU_SURE}
    </div>
    <div class="content">
        {$CONFIRM_SECRET_REGEN}
    </div>
    <div class="actions">
        <a class="ui positive button">{$NO}</a>
        <form action="" method="post" style="display: inline;">
            <input type="hidden" name="token" value="{$TOKEN}" />
            <input type="hidden" name="action" value="regen" />
            <input type="submit" class="ui negative button" value="{$REGEN}" />
        </form>
    </div>
</div>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        // Define the base OAuth2 URL from Smarty variable
        const baseOAuthUrl = '{$OAUTH2_URL_VALUE}';

        // Select all checkboxes with name="scopes"
        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="scopes"]');

        // Select the oauth_url input field
        const oauthUrlField = document.querySelector('input[name="oauth_url"]');

        // Function to update oauth_url based on selected checkboxes
        function updateOAuthUrl() {
            // Get values of checked checkboxes
            const selectedScopes = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            // Build the scope string (joined with '+' or empty if none selected)
            const scopeString = selectedScopes.length > 0 ? selectedScopes.join('+') : '';

            // Update oauth_url field
            oauthUrlField.value = selectedScopes.length > 0 ? baseOAuthUrl + scopeString : '';
        }

        // Attach change event listener to each checkbox
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateOAuthUrl);
        });

        // Initialize oauth_url on page load
        updateOAuthUrl();
    });
</script>

{include file='footer.tpl'}