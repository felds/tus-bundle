# To do

* [_] What to do with the Upload-Offset when it doesn't match the already uploaded contents?
    > The Upload-Offset header’s value MUST be equal to the current offset of the resource.
        In order to achieve parallel upload the Concatenation extension MAY be used. If the offsets do not match,
        the Server MUST respond with the 409 Conflict status without modifying the upload resource.
* [_] Return a `412 Precondition Failed` when protocols don't match
* [_] How to deal with `X-HTTP-Method-Override`? Is it even possible with symfony?
* [_] Return `415 Unsupported Media Type` when client sends content types other than `application/offset+octet-stream`
* [_] Implement `410 Gone` when trying to access 
