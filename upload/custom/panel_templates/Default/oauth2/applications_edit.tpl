{include file='header.tpl'}

<body id="page-top">

    <!-- Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        {include file='sidebar.tpl'}

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main content -->
            <div id="content">

                <!-- Topbar -->
                {include file='navbar.tpl'}

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">{$APPLICATIONS}</h1>
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{$PANEL_INDEX}">{$DASHBOARD}</a></li>
                            <li class="breadcrumb-item active">{$APPLICATIONS}</li>
                        </ol>
                    </div>

                    <!-- Update Notification -->
                    {include file='includes/update.tpl'}

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5 style="display:inline">{$APPLICATION_TITLE}</h5>
                            <div class="float-md-right">
                                <a href="{$BACK_LINK}" class="btn btn-warning">{$BACK}</a>
                            </div>
                            <hr />

                            <!-- Success and Error Alerts -->
                            {include file='includes/alerts.tpl'}

                            <form role="form" action="" method="post">
                                <div class="form-group">
                                    <label for="InputClientID">Client ID</label>
                                    <div class="input-group">
                                        <input type="text" name="client_id" class="form-control" id="InputClientID" value="{$CLIENT_ID_VALUE}" readonly>
                                        <span class="input-group-append"><a onclick="copyClientID();" class="btn btn-info text-white">{$COPY}</a></span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="InputClientSecret">Client Secret</label>
                                    <div class="input-group">
                                        <input type="text" name="client_secret" class="form-control" id="InputClientSecret" value="{$CLIENT_SECRET_VALUE}" readonly>
                                        <span class="input-group-append"><a onclick="showRegenModal();" class="btn btn-info text-white">{$CHANGE}</a></span>
                                        <span class="input-group-append"><a onclick="copyClientSecret();" class="btn btn-info text-white">{$COPY}</a></span>
                                    </div>
                                </div>

                                <hr />

                                <div class="form-group">
                                    <label for="InputName">{$NAME}</label>
                                    <input type="text" name="name" class="form-control" id="InputName" placeholder="{$NAME}" value="{$NAME_VALUE}">
                                </div>
                                <div class="form-group">
                                    <label for="InputUrl">{$REDIRECT_URI}</label>
                                    <input type="text" name="redirect_uri" class="form-control" id="InputRedirectURI" placeholder="{$REDIRECT_URI}" value="{$REDIRECT_URI_VALUE}">
                                </div>
                                <div class="form-group custom-control custom-switch">
                                    <input id="inputSkipApproval" name="skip_approval" type="checkbox" class="custom-control-input"{if $SKIP_APPROVAL_VALUE eq 1} checked{/if} />
                                    <label class="custom-control-label" for="inputSkipApproval">
                                        Ship OAuth2 approval?
                                    </label>
                                </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                                <h5 style="display:inline">Integrate another NamelessMC website</h5>
                                <hr />
                                <div class="form-group custom-control custom-switch">
                                    <input id="inputNamelessIntegration" name="nameless_integration" type="checkbox" class="custom-control-input"{if $NAMELESS_INTEGRATION_VALUE eq 1} checked{/if} />
                                    <label class="custom-control-label" for="inputNamelessIntegration">
                                        Add integration & OAuth
                                    </label>
                                </div>
                                <div class="form-group custom-control custom-switch">
                                    <input id="inputSyncGroups" name="sync_groups" type="checkbox" class="custom-control-input"{if $SYNC_GROUPS_VALUE eq 1} checked{/if} />
                                    <label class="custom-control-label" for="inputSyncGroups">
                                        Sync groups?
                                    </label>
                                </div>
                                <div class="form-group custom-control custom-switch">
                                    <input id="inputSyncIntegrations" name="sync_integrations" type="checkbox" class="custom-control-input"{if $SYNC_INTEGRATIONS_VALUE eq 1} checked{/if} />
                                    <label class="custom-control-label" for="inputSyncIntegrations">
                                        Sync integrations?
                                    </label>
                                </div>
                                <div class="form-group">
                                    <label for="InputNamelessURL">Website URL</label>
                                    <input type="text" name="nameless_url" class="form-control" id="InputNamelessURL" placeholder="Website URL" value="{$NAMELESS_URL_VALUE}">
                                </div>
                                <div class="form-group">
                                    <label for="InputNamelessClientId">Client ID</label>
                                    <input type="text" name="nameless_client_id" class="form-control" id="InputNamelessClientId" placeholder="Client ID" value="{$NAMELESS_CLIENT_ID_VALUE}">
                                </div>
                                <div class="form-group">
                                    <label for="InputNamelessAPIKey">API Key</label>
                                    <input type="text" name="nameless_api_key" class="form-control" id="InputNamelessAPIKey" placeholder="API Key" value="{$NAMELESS_API_KEY_VALUE}">
                                </div>
                                    
                                <div class="form-group">
                                    <input type="hidden" name="token" value="{$TOKEN}">
                                    <input type="submit" class="btn btn-primary" value="{$SUBMIT}">
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5 style="display:inline">{$OAUTH2_URL_GENERATOR}</h5>
                            <hr />
                            <form role="form" action="" method="post">
                                <div class="form-group">
                                    <label for="InputOAuth2URL">{$OAUTH2_URL}</label>
                                    <div class="input-group">
                                        <input type="text" name="oauth_url" class="form-control" id="InputOAuth2URL" placeholder="{$SELECT_SCOPES_TO_GENERATE}" readonly>
                                        <span class="input-group-append"><a onclick="copyAuthURL();" class="btn btn-info text-white">{$COPY}</a></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="InputOAuth2URL">{$SCOPES}</label>
                                    <div class="row">
                                        {foreach from=$SCOPES_LIST key=scope item=item}
                                            <div class="col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="scopes" value="{$scope}" id="defaultCheck{$scope}">
                                                    <label class="form-check-label" for="defaultCheck{$scope}">{$scope}</label>
                                                </div>
                                            </div>
                                        {/foreach}
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Spacing -->
                    <div style="height:1rem;"></div>

                    <!-- End Page Content -->
                </div>

                <!-- End Main Content -->
            </div>

            {include file='footer.tpl'}

            <!-- End Content Wrapper -->
        </div>

        <!-- Client secret regen modal -->
        <div class="modal fade" id="regenModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{$ARE_YOU_SURE}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        {$CONFIRM_SECRET_REGEN}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{$NO}</button>
                        <button type="button" onclick="regenSecret()" class="btn btn-primary">{$YES}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- End Wrapper -->
    </div>

    {include file='scripts.tpl'}
    
    <script type="text/javascript">
        function showRegenModal() {
            $('#regenModal').modal().show();
        }

        function regenSecret() {
            const regen = $.post("{$REGEN_CLIENT_SECRET_LINK}", { action: 'regen', token: "{$TOKEN}" });
            regen.done(function () { window.location.reload(); })
        }

        function copyClientID() {
            let url = document.getElementById("InputClientID");
            url.select();
            document.execCommand("copy");

            // Toast
            $('body').toast({
                showIcon: 'fa-solid fa-check move-right',
                message: '{$COPIED}',
                class: 'success',
                progressUp: true,
                displayTime: 6000,
                showProgress: 'bottom',
                pauseOnHover: false,
                position: 'bottom left',
            });
        }

        function copyClientSecret() {
            let url = document.getElementById("InputClientSecret");
            url.select();
            document.execCommand("copy");

            // Toast
            $('body').toast({
                showIcon: 'fa-solid fa-check move-right',
                message: '{$COPIED}',
                class: 'success',
                progressUp: true,
                displayTime: 6000,
                showProgress: 'bottom',
                pauseOnHover: false,
                position: 'bottom left',
            });
        }

        function copyAuthURL() {
            let url = document.getElementById("InputOAuth2URL");
            url.select();
            document.execCommand("copy");

            // Toast
            $('body').toast({
                showIcon: 'fa-solid fa-check move-right',
                message: '{$COPIED}',
                class: 'success',
                progressUp: true,
                displayTime: 6000,
                showProgress: 'bottom',
                pauseOnHover: false,
                position: 'bottom left',
            });
        }
    </script>

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

</body>

</html>