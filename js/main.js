//TODO: Собирать вебпаком с Babel, использовать ES6
$(function() {
    var collectionId = "";
    var $collectionTableBody = $('#collection-table-body');
    var $collectionTableWrapper = $('#collection-table-wrapper');

    $('form#collection-link').submit(function(e) {
        var $form = $(this);
        var $input = $('#collection-input').val();

        $collectionTableWrapper.slideUp('fast');
        $collectionTableBody.empty();

        if ($input) {
            $.ajax({
                type: $form.attr('method'),
                url: $form.attr('action'),
                data: {
                    type: 'collection-send',
                    value: $input,
                }
            }).done(function(data) {
                if (data.items) {
                    $.toast({
                        title: 'Успех!',
                        subtitle: 'Коллекция загружена',
                        type: 'success',
                        delay: 1000
                    });

                    $.each(data.items, function(i, item) {
                        var $tr = $('<tr>').append(
                            $('<td data-editor="input" data-editor-type="number"    class="inline-editor num">').text(item.num),
                            $('<td data-editor="textarea" data-editor-type="text"   class="inline-editor title">').text(item.title),
                            $('<td data-editor="img" data-editor-type="text"        class="inline-editor picture">').append(
                                $('<img src="'+item.picture+'" alt="'+item.title+'">')
                            ),
                            $('<td data-editor="input" data-editor-type="number"    class="inline-editor price">').text(item.price),
                            $('<td data-editor="textarea" data-editor-type="text"   class="inline-editor desc">').text(item.desc)
                        );
                        $tr.appendTo('#collection-table-body');
                        $collectionTableWrapper.slideDown('fast');
                    });

                    collectionId = data.collectionId;

                } else {
                    if (data.errors) {
                        $.toast({
                            title: 'Ошибка',
                            subtitle: '',
                            content: data.errors[0],
                            type: 'error',
                            delay: 5000
                        });
                    }
                    else {
                        $.toast({
                            title: 'Ошибка',
                            subtitle: '',
                            content: 'Неизвестная ошибка :(',
                            type: 'error',
                            delay: 3000
                        });
                    }
                }


            }).fail(function() {
                $.toast({
                    title: 'Ошибка',
                    subtitle: '',
                    content: 'Сервер не ответил :(',
                    type: 'error',
                    delay: 1000
                });
            });
        }

        e.preventDefault();
    });

    $collectionTableBody.on('dblclick', '.inline-editor', function () {
        var oldContent = $(this).text();
        var editor = '<input type="text" />';
        switch ($(this).data('editor')) {
            case "input":
                editor = '<input type="' + $(this).data('editor-type') + '"/>';
                break;
            case "textarea":
                editor = '<textarea rows="6"/>';
                break;
            case "img":
                editor = '<input type="' + $(this).data('editor-type') + '"/>';
                oldContent = $(this).children("img").attr('src');
                break;
        }

        $(this).removeClass('inline-editor')
            .empty()
            .append(
                $(editor)
                    .addClass('inline-editor-active')
                    .val(oldContent)
            );
    });

    $collectionTableBody.on('blur', '.inline-editor-active', function (e) {
        let newContent = $(this).val();
        $(this).removeClass('inline-editor-active');

        if ( $(this).parent().data('editor') === 'img' ) {
            let src = newContent;
            let _this = this;

            checkImg(src, _this)
                .then(
                    function (_this) {
                        console.log($(_this));
                        $(_this).parent()
                            .addClass('inline-editor')
                            .html(
                            $('<img src="'+newContent+'" alt="">')
                        );
                        $.toast({
                            title: 'Успех!',
                            subtitle: 'Изображение загружено',
                            type: 'success',
                            delay: 1000
                        });
                    },
                    // error
                    function (_this) {
                        console.log($(_this));
                        $(_this).addClass('inline-editor-active');
                        $.toast({
                            title: 'Ошибка',
                            subtitle: 'Проверьте URL',
                            content: 'Произошла ошибка при загрузке изображения.',
                            type: 'error',
                            delay: 1000
                        });
                    }
                );
        }
        else {
            $(this).parent().text(newContent).addClass('inline-editor');
        }

        e.preventDefault();
    });

    $('#save-csv-btn').on('click touch', function () {
        var $inlineEditorActive = $('.inline-editor-active');
        if($inlineEditorActive.length !== 0) {

            $.toast({
                title: 'Ошибка',
                content: 'Сохраните все изменения',
                type: 'error',
                delay: 1000
            });

            $inlineEditorActive.css('border', '2px solid yellow');

            return;
        }

        var editedCollection = $collectionTableBody.find('tr').get().map(function(row) {
            return $(row).find('td').get().map(function(cell) {
                return $(cell).data('editor') === "img" ? $(cell).children("img").attr('src') : $(cell).text();
            });
        });

        $.ajax({
            type: "POST",
            url:  "./api/index.php",
            data: {
                type: 'save-to-csv',
                value: JSON.stringify(editedCollection),
                collectionId: collectionId
            }
        })
            .done(function(data) {
                console.log('data', data);
                $.toast({
                    title: 'Супер!',
                    subtitle: '',
                    content: 'Скачивание начинается',
                    type: 'success',
                    delay: 1000
                });
                downloadURI(data.url);
            })
            .fail(function() {
                $.toast({
                    title: 'Ошибка',
                    subtitle: '',
                    content: 'Сервер не ответил :(',
                    type: 'error',
                    delay: 1000
                });
        });

    });

    function checkImg(url,_this) {
        return new Promise(function(resolve, reject) {
            $.get("https://cors-anywhere.herokuapp.com/"+url)
                .done(function() {
                    console.log('checkImg true', url);
                    resolve(_this);
                }).fail(function() {
                console.log('checkImg false', url);
                reject(_this);
            })

        });
    }
    function downloadURI(uri) {
        //Тут изначально был лапшекод по созданию ссылки, аттачу её в body, click() и remove ссылки.
        //Но потом я нашел такой способ:
        window.location.href = uri;
    }
});


