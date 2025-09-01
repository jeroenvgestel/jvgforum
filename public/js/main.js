async function MarkAllRead()
{
    const url = baseurl + 'forum/markallread';
    await getAndReload(url);
}

/**
 * Posts data async, expects Response
 * @param postUrl
 * @param postData
 * @returns {Promise<{status: number, message}|any>}
 */
async function sendPostRequest(postUrl, postData)
{
    const response = await fetch(postUrl, {
        method: 'post',
        headers: {
            'content-type': 'application/json'
        },
        body: JSON.stringify(postData)
    });

    const text = await response.text();

    try
    {
        return JSON.parse(text);
    }
    catch(error)
    {
        console.error(error, text);

        return {
            errorCode: 500,
            error: error.message,
            success: false
        }
    }
}

/**
 * Posts data async, expects Response
 * @param getUrl
 * @returns {Promise<{status: number, message}|any>}
 */
async function sendGetRequest(getUrl)
{
    const response = await fetch(getUrl, {
        method: 'get',
        headers: {
            'content-type': 'application/json'
        }
    });

    const text = await response.text();

    try
    {
        return JSON.parse(text);
    }
    catch(error)
    {
        console.error(error, text);

        return {
            errorCode: 500,
            error: error.message,
            success: false
        }
    }
}

async function getAndReload(getUrl)
{
    const data = await sendGetRequest(getUrl);

    if(data.success === false) {
        showErrorDialog(data.message);
        return;
    }

    location.reload();
}

async function postAndRedirect(postUrl, postData)
{
    const response = await sendPostRequest(postUrl, postData);

    if(!response.success) {
        showErrorDialog(response.message);
        return;
    }

    if(response.redirect.length > 0) {
        redirect(response.redirect);
    }
}

function showErrorDialog(message)
{
    Swal.fire({
        icon: "error",
        html: message,
    });
}

function redirect(url)
{
    window.location.href = url;
}

function imageHandler()
{
    const range = this.quill.getSelection();
    const value = prompt('What is the image URL');
    if (value) {
        this.quill.insertEmbed(range.index, 'image', value, Quill.sources.USER);
    }
}

