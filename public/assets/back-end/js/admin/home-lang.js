    const languages = @json($languages);
    const jsonData = @json($jsonData['section']['cards'] ?? []);

    document.querySelectorAll('.form-system-language-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const selectedLang = this.id.split('-')[0]; // e.g. 'en'

            // Remove active class from all tabs and add to clicked tab
            document.querySelectorAll('.form-system-language-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Loop through all cards and update input values based on selected language
            jsonData.forEach((card, index) => {
                ['title', 'image_alt', 'description'].forEach(field => {
                    const selector = `input[data-field="${field}"][data-index="${index}"], textarea[data-field="${field}"][data-index="${index}"]`;
                    const input = document.querySelector(selector);
                    if (input) {
                        input.name = `cards[${index}][${field}][${selectedLang}]`;

                        let val = (card[field] && card[field][selectedLang]) ? card[field][selectedLang] : '';
                        input.value = val;
                        if (input.tagName.toLowerCase() === 'textarea') {
                            input.textContent = val;
                        }
                    }
                });
            });
        });
    });
