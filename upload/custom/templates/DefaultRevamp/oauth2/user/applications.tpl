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
                <h3 class="ui header">{$APPLICATIONS}
                    {if isset($NEW_APPLICATION)}
                        <div class="res right floated">
                            <a class="ui mini primary button" href="{$NEW_APPLICATION_LINK}">{$NEW_APPLICATION}</a>
                        </div>
                    {/if}
                </h3>

                {nocache}
                    {if isset($APPLICATIONS_LIST)}
                        {foreach from=$APPLICATIONS_LIST item=app}
                            <div class="ui divider"></div>
                            {$app.name}<div class="res right floated"><a class="ui mini primary button" href="{$app.edit_link}">View</a></div>
                        {/foreach}
                    {else}
                        <div class="ui info message">
                            <div class="content">
                                {$NO_APPLICATIONS}
                            </div>
                        </div>
                    {/if}
                {/nocache}
            </div>
        </div>
    </div>
</div>

{include file='footer.tpl'}