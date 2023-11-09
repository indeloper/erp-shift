
@php
    if(str_contains($text, 'notificationHook_')) {
        $hookTypeAndId = explode('notificationHook_', explode('_endNotificationHook', $text)[0])[1];
        $text = $text.' <div id="'.$hookTypeAndId.'"></div>';
        $hookJSButton = '
            <script>
                function mountHookButton() {
                    let checkAnchorDivIsAvailable = setInterval(() => {
                            if($("#'.$hookTypeAndId.'")) {
                                
                                clearInterval(checkAnchorDivIsAvailable)

                                $("#'.$hookTypeAndId.'").dxButton({
                                    text: "Выполнить",
                                    onClick() {
                                        hookHandlerDispatcher("'.$hookTypeAndId.'")
                                    }
                                })
                            }
                    }, 1000)
                    
                }
                mountHookButton()        
            </script>
        ';
        $text = str_replace('notificationHook_'.$hookTypeAndId.'_endNotificationHook', $hookJSButton, $text);
    }
    
@endphp

<td data-label="Уведомления">{!!$text!!}</td>