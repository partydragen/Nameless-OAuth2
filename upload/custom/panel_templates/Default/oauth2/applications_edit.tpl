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
    </script>

</body>

</html>