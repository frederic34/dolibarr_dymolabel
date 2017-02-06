// Copyright (C) 2015-2016  Frederic France     <frederic.france@free.fr>
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// or see http://www.gnu.org/
//

//
// \file       htdocs/dymolabel/js/productdymo.js
// \brief      File that include javascript functions for dymolabel
//

(function()
{
    // stores loaded label info
    var label;

    function escapeXml(xmlStr)
    {
        var result = xmlStr;
        var findReplace = [[/&/g, "&amp;"], [/</g, "&lt;"], [/>/g, "&gt;"], [/"/g, "&quot;"]];

        for(var i = 0; i < findReplace.length; ++i) 
            result = result.replace(findReplace[i][0], findReplace[i][1]);

        return result;
    }

    // called when the document completly loaded
    function onload()
    {
        var printersSelect = document.getElementById('printersSelect');
        var textInput = document.getElementById('textInput');
        var printButton = document.getElementById('printButton');
        
        
        var textMarkupInput = document.getElementById('textMarkupInput');
        var printTextMarkupButton = document.getElementById('printTextMarkupButton');
        
        

        // loads all supported printers into a combo box 
        function loadPrinters()
        {
            var printers = dymo.label.framework.getPrinters();
            if (printers.length == 0)
            {
                alert("No DYMO printers are installed. Install DYMO printers.");
                return;
            }

            for (var i = 0; i < printers.length; i++)
            {
                var printer = printers[i];
                if (printer.printerType == "LabelWriterPrinter")
                {
                    var printerName = printer.name;

                    var option = document.createElement('option');
                    option.value = printerName;
                    option.appendChild(document.createTextNode(printerName));
                    printersSelect.appendChild(option);
                }
            }
        }


        function updatePreview() {
            try {
                var address = textInput.value;
                label.setAddressText(0, address);
                if (!label)
                {
                    alert("Update preview");
                    return;
                }

                var pngData = label.render();
                var PreviewImageSrc = document.getElementById('PreviewImageSrc');

                PreviewImageSrc.src = "data:image/png;base64," + pngData;
            }
            catch (e) {
                alert(e.message);
            }
        }



        printButton.onclick = function()
        {
            try
            {
                if (!label)
                {
                    alert("Load label before printing");
                    return;
                }

                // set data using LabelSet and text markup
                var labelSet = new dymo.label.framework.LabelSetBuilder();
                var record = labelSet.addRecord();

                //var text = textInput.value.split(' ');
                var text = textInput.value;

                var textMarkup = '';
                for (var i = 0; i < text.length; ++i)
                {
                    textMarkup += '<font family="Arial" size="16">' + escapeXml(text[i]) + '</font>';

                }

                textMarkupInput.value = textMarkup;
                record.setTextMarkup('Adresse', textMarkup);
                label.print(printersSelect.value, null, labelSet.toString());
            }
            catch(e)
            {
                alert(e.message || e);
            }
        }



        printTextMarkupButton.onclick = function()
        {
            try
            {
                if (!label)
                {
                    alert("Load label before printing");
                    return;
                }

                // set data using LabelSet and text markup
                var labelSet = new dymo.label.framework.LabelSetBuilder();
                var record = labelSet.addRecord();

                record.setTextMarkup('Adresse', textMarkupInput.value);
                label.print(printersSelect.value, null, labelSet.toString());
            }
            catch(e)
            {
                alert(e.message || e);
            }
        }

        function loadLabelFromWeb()
        {                     
            // use jQuery API to load label
            $.get("Address.label", function(labelXml)
            {
                label = dymo.label.framework.openLabelXml(labelXml);
                updatePreview();
            }, "text");
        }
        

        // load printers list on startup
        loadPrinters();

        loadLabelFromWeb();
    };

    // register onload event
    if (window.addEventListener)
        window.addEventListener("load", onload, false);
    else if (window.attachEvent)
        window.attachEvent("onload", onload);
    else
        window.onload = onload;

} ());
