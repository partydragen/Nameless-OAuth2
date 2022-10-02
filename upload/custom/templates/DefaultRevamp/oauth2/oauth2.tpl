{include file='header.tpl'}
{include file='navbar.tpl'}

{if isset($ERRORS)}
  <div class="ui error icon message">
    <i class="x icon"></i>
    <div class="content">
      <div class="header">{$ERROR_TITLE}</div>
      <ul class="list">
        {foreach from=$ERRORS item=error}
          <li>{$error}</li>
        {/foreach}
      </ul>
    </div>
  </div>
{/if}

{if isset($SUCCESS)}
  <div class="ui success icon message">
    <i class="check icon"></i>
    <div class="content">
      <div class="header">{$SUCCESS_TITLE}</div>
      {$SUCCESS}
    </div>
  </div>
{/if}

{if isset($ACCESS_TO)}
    <div class="ui stackable grid">
        <div class="ui centered row">
            <div class="ui padded segment sixteen wide tablet ten wide computer column">
                <h3 class="ui header">{$APPLICATION_WANTS_ACCESS}</h3>
                <div class="ui divider"></div>

                <p>{$APPLICATION_WANTS_INFORMATION}</p>
                <ul class="ui list">
                    {foreach from=$ACCESS_TO item=item}
                        <div class="item"><i class="icon checkmark green"></i> <div class="content">
                            {$item}
                        </div></div>
                    {/foreach}
                </ul>

                <form class="ui form" action="" method="post" id="form-login">
                    <input type="hidden" name="token" value="{$TOKEN}">
                    <input type="submit" class="ui positive button" value="{$AUTHORIZE}">
                    <a class="ui negative button" href="{$CANCEL_LINK}">{$CANCEL}</a>
                </form>
            </div>
        </div>
    </div>
{/if}

{include file='footer.tpl'}
