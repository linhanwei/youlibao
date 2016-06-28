/**
 * Created by xiuxiu on 2016/6/27.
 */
$('body').on('click', '[href]', touchAction)

function touchAction(event) {
    console.log(event, this);
    var sender = $(this),
        href = sender.attr('href');
    if (href) {
        location.href = href;
    }
}