<html>

<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Tesseract.js OCR demo</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/ocr.css">
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://kit.fontawesome.com/4414288e8e.js"></script>
    <script src='js/tesseract.min.js'></script>
</head>

<body>
    <main>
        <div class="container mt-3">
            <div class="row">
                <div class="col-12 col-md-4 mt-3 mt-md-0">
                    <div class="box">
                        <input type="file" name="file-1[]" id="file-1" class="inputfile inputfile-1"
                            data-multiple-caption="{count} files selected" multiple />
                        <label for="file-1"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17"
                                viewBox="0 0 20 17">
                                <path
                                    d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z" />
                            </svg> <span>Choose a file&hellip;</span></label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-md-5">
                    <div class="image-container"><img id="selected-image" src=""
                            class="col-12 p-0" /></div>
                </div>
                <div class="col-12 col-md-1">
                    <i id="arrow-right" class="fas fa-arrow-right d-none d-md-block"></i>
                    <i id="arrow-down" class="fas fa-arrow-down d-block d-md-none"></i>
                </div>
                <div class="col-12 col-md-6">
                    <div id="log">
                        <span id="startPre">
                            <a id="startLink" href="#"></a>
                            <br />
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-10">
                    <div >
                        <ul id="word">
                           
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row d-none" id="fields">
                <div class="col-md-12">
                    <label class="form-label my-2">Name</label>
                    <input type="text" class="form-control" id="name" value="" />
                </div>
                <div class="col-md-6">
                    <label class="form-label my-2">Father Name</label>
                    <input type="text" class="form-control" id="father_name" value="" />
                </div>
                <div class="col-md-6">
                    <label class="form-label my-2">Mother Name</label>
                    <input type="text" class="form-control" id="mother_name" value="" />
                </div>
                <div class="col-md-6">
                    <label class="form-label my-2">Date Of Birth</label>
                    <input type="text" class="form-control" id="date_of_birth" value="" />
                </div>
                <div class="col-md-6">
                    <label class="form-label my-2">Id No</label>
                    <input type="text" class="form-control" id="id_no" value="" />
                </div>
            </div>
        </div>
    </main>
    {{-- <script src="js/tesseract-ocr.js"></script> --}}
    <script>
        $(document).ready(function() {
            var inputs = document.querySelectorAll('.inputfile');
            Array.prototype.forEach.call(inputs, function(input) {
                var label = input.nextElementSibling,
                    labelVal = label.innerHTML;

                input.addEventListener('change', function(e) {
                    var fileName = '';
                    if (this.files && this.files.length > 1)
                        fileName = (this.getAttribute('data-multiple-caption') || '').replace(
                            '{count}', this.files.length);
                    else
                        fileName = e.target.value.split('\\').pop();

                    if (fileName) {
                        label.querySelector('span').innerHTML = fileName;

                        let reader = new FileReader();
                        reader.onload = function() {
                            let dataURL = reader.result;
                            $("#selected-image").attr("src", dataURL);
                            $("#selected-image").addClass("col-12");
                        }
                        let file = this.files[0];
                        reader.readAsDataURL(file);
                        startRecognize(file);
                    } else {
                        label.innerHTML = labelVal;
                        $("#selected-image").attr("src", '');
                        $("#selected-image").removeClass("col-12");
                        $("#arrow-right").addClass("fa-arrow-right");
                        $("#arrow-right").removeClass("fa-check");
                        $("#arrow-right").removeClass("fa-spinner fa-spin");
                        $("#arrow-down").addClass("fa-arrow-down");
                        $("#arrow-down").removeClass("fa-check");
                        $("#arrow-down").removeClass("fa-spinner fa-spin");
                        $("#log").empty();
                    }
                });

                // Firefox bug fix
                input.addEventListener('focus', function() {
                    input.classList.add('has-focus');
                });
                input.addEventListener('blur', function() {
                    input.classList.remove('has-focus');
                });
            });
        });

        $("#startLink").click(function() {
            var img = document.getElementById('selected-image');
            startRecognize(img);
        });

        function startRecognize(img) {
            $("#arrow-right").removeClass("fa-arrow-right");
            $("#arrow-right").addClass("fa-spinner fa-spin");
            $("#arrow-down").removeClass("fa-arrow-down");
            $("#arrow-down").addClass("fa-spinner fa-spin");
            recognizeFile(img);
        }

        function progressUpdate(packet) {
            var log = document.getElementById('log');

            if (log.firstChild && log.firstChild.status === packet.status) {
                if ('progress' in packet) {
                    var progress = log.firstChild.querySelector('progress')
                    progress.value = packet.progress
                }
            } else {
                var line = document.createElement('div');
                line.status = packet.status;
                var status = document.createElement('div')
                status.className = 'status'
                status.appendChild(document.createTextNode(packet.status))
                line.appendChild(status)

                if ('progress' in packet) {
                    var progress = document.createElement('progress')
                    progress.value = packet.progress
                    progress.max = 1
                    line.appendChild(progress)
                }


                if (packet.status == 'done') {
                    log.innerHTML = ''
                    var pre = document.createElement('pre')
                    pre.appendChild(document.createTextNode(packet.data.text.replace(/\n\s*\n/g, '\n')))
                    line.innerHTML = ''
                    line.appendChild(pre)
                    $(".fas").removeClass('fa-spinner fa-spin')
                    $(".fas").addClass('fa-check')
                }

                log.insertBefore(line, log.firstChild)
            }
        }

        function recognizeFile(file) {
            $("#log").empty();
            const corePath = window.navigator.userAgent.indexOf("Edge") > -1 ?
                'js/tesseract-core.asm.js' :
                'js/tesseract-core.wasm.js';


            const worker = new Tesseract.TesseractWorker({
                corePath,
            });

            worker.recognize(file,
                    'eng+ben'
                )
                .progress(function(packet) {
                    console.info(packet)
                    progressUpdate(packet)

                })
                .then(function(data) {
                    
                    let text_content = data.words.map(word => { return word.text});

                    // let html = '';

                    // for(let i=0; i< text_content.length; i++){
                    //     html += '<li>'+i+' '+text_content[i]+'</li>'
                    // }
                    // $('#word').append(html);
                  
                    name_index = text_content.indexOf('নাম:');
                    asg_index = text_content.indexOf('Name:');
                    father_index = text_content.indexOf('পিতা:');
                    mother_index = text_content.indexOf('মাতা;');
                    date_index = text_content.indexOf('Date');
                    birth_index = text_content.indexOf('Birth:');
                    id_no_index = text_content.indexOf('NO:')

                    name = text_content.slice(name_index+1,asg_index).toString();
                    father_name = text_content.slice(father_index+1,father_index+4).toString();
                    mother_name = text_content.slice(mother_index+1,mother_index+4).toString();
                    date_of_birth = text_content.slice(birth_index+1,birth_index+4).toString();
                    id_no = text_content.slice(id_no_index+1,id_no_index+2).toString();
        
                  
                    $('#name').val(name.replaceAll(',',' '));
                    $('#father_name').val(father_name.replaceAll(',',' '));
                    $('#mother_name').val(mother_name.replaceAll(',',' '));
                    $('#date_of_birth').val(date_of_birth.replaceAll(',',' '));
                    $('#id_no').val(id_no);

                    $('#fields').removeClass('d-none');

                    progressUpdate({
                        status: 'done',
                        data: data
                    })
                })
        }

    </script>
</body>

</html>
