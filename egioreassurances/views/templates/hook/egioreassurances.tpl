{if count($reassurs)}
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="row">
            {foreach from=$reassurs item=re}
                <div class="col-sm-{$cols} row">
                    <a href="{$re['link']}" target="{if $re['new_tab']}_blanc{/if}" class="d-block">
                        <div class="col-sm-6">
                        <img
                            style="width: {$icon_width}; height: {$icon_hight}"
                            src="{$img_basepath}{$re['icon']}"
                            alt="{$re['alt']}"
                            class="d-inline-block">
                        </div>

                        <div class="d-inline-block col-sm-6">
                            <p style="word-wrap: break-word;">{$re['libelle']}</p>
                            <p style="word-wrap: break-word;">{substr($re['description'], 1, 100)}</p>
                        </div>
                    </a>
                </div>
            {/foreach}

        </div>
    </div>
{/if}