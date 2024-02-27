class ___script___ {

    static run(url, asynchronous = false, s = null, e = null) {
        $.ajax({
            url: url,
            dataType: "script",
            async: asynchronous,
            success: s,
            error: e
        });
    }

}