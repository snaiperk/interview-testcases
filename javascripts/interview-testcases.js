/*  Проект "interview-testcases"
*   Основной скрипт отображения проекта
*
*   Здесь не будет каких-то особых наворотов, т.к. я всё-таки собеседуюсь в основном на PHP-разработку, а не на JS.
*   Но при желании могу завернуть, это да, было дело...
*
*   Автор - Игорь Ондар
*           io@azbukatuva.ru
*/

function main()
{
    $(document).ready(function(){
        // 1. Привязываем кнопки "свернуть-развернуть"
        $('*[id]').each(function(){
            if (this.id.slice(-7) == '_button') {
                var c_id = this.id.substring(0, this.id.length-7)+'_content';
                var content = $('#'+c_id);
                if (content.length >= 0) {
                    $(this).on('click', function(){
                        content.toggle('fast');
                        $(this).toggleClass('clickable');
                        //alert(c_id);
                    });
                    $(this).toggleClass('clickable');
                    content.hide();
                }
            }
        });
        // 2. Устанавливаем фон документа и "стеклянного" слоя.
        setBackground();
    });
}

function setBackground()
{
    var tmpStyleCSS = "{ background-image: url('/images/"+backgroundName+"');}";
    $("<style type='text/css'>\n\t.bgPicture "+tmpStyleCSS+"\n\t.glass:before "+tmpStyleCSS+" </style>").appendTo("head");
    $(".testcase-select-document").addClass('bgPicture');
    $(".testcase-glass-matt").addClass('glass');
    //alert('Устанавливаю фон: '+backgroundName);    
}

main();