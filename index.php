<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Analysis - PDF Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .section-card {
            border-radius: 8px;
        }
        .section-header {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        .section-content {
            padding: 8px;
            padding-top: 5px;
            padding-bottom: 0px;
            overflow: auto;
        }
        .card-body{
            padding: 0.9rem;
        }
        .key-value-row {
            background-color: #ffffff;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 0px;
            transition: all 0.2s ease;
        }
        .bulk-edit-textarea {
            resize: vertical;
            min-height: 60px;
            width: 100%;
            transition: border-color 0.3s ease;
        }
        .bulk-edit-textarea.border-warning {
            border-color: #ffc107 !important;
            border-width: 2px;
        }
        .bulk-edit-textarea:disabled {
            background-color: #f8f9fa;
            cursor: not-allowed;
        }
        .template-radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }
        .template-radio {
            display: flex;
            align-items: center;
        }
        .template-radio input[type="radio"] {
            margin-right: 5px;
        }
        .template-radio label {
            margin-bottom: 0;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            transition: background-color 0.2s;
        }
        .template-radio:hover label {
            background-color: #f0f0f0;
        }
        .template-radio input[type="radio"]:checked + label {
            background-color: #e7f3ff;
            font-weight: 500;
        }
        /* Searchable dropdown styles */
        .searchable-dropdown {
            position: relative;
            width: 100%;
        }
        .searchable-dropdown-toggle {
            width: 100%;
            text-align: left;
            background-color: white;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            padding-right: 2rem;
        }
        .searchable-dropdown-toggle:hover {
            border-color: #86b7fe;
        }
        .searchable-dropdown-toggle:focus {
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgb(13 110 253 / 25%);
        }
        .searchable-dropdown-toggle::after {
            content: "▼";
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8em;
        }
        .searchable-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            display: none;
            background-color: white;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            max-height: 300px;
            overflow: hidden;
            margin-top: 2px;
        }
        .searchable-dropdown-menu.show {
            display: block;
        }
        .searchable-dropdown-search {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            position: sticky;
            top: 0;
            background-color: white;
            z-index: 1;
        }
        .searchable-dropdown-search input {
            width: 100%;
            padding: 0.375rem 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .searchable-dropdown-search input:focus {
            outline: none;
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgb(13 110 253 / 25%);
        }
        .searchable-dropdown-items {
            max-height: 250px;
            overflow-y: auto;
        }
        .searchable-dropdown-item {
            padding: 0.5rem 1rem;
            cursor: pointer;
            transition: background-color 0.15s;
        }
        .searchable-dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .searchable-dropdown-item.selected {
            background-color: #e9ecef;
            font-weight: 500;
        }
        .searchable-dropdown-item.hidden {
            display: none;
        }
        .searchable-dropdown-no-results {
            padding: 1rem;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
        .btn-outline-success {
            color: #28a745;
            border-color: #28a745;
            background-color: transparent;
        }
        .btn-outline-success:hover {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }
        /* Button group styling */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }
        .button-divider {
            width: 1px;
            height: 30px;
            background-color: #dee2e6;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-file-pdf me-2"></i>
                Certificate of Analysis Generator
            </span>
        </div>
    </nav>

    <div class="container mt-2">
        <!-- Selection Form -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Template Selection with Radio Buttons -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Template
                                </label>
                                <div id="templateRadioButtons" class="d-flex flex-wrap gap-3">
                                    <div class="text-muted">
                                        <i class="fas fa-spinner fa-spin me-1"></i>
                                        Loading templates...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catalog and Lot Selection -->
                        <div class="row">   
                            <div class="col-md-4">
                                <label for="catalogSelect" class="form-label" style="color: red;">Catalog Number</label>
                                <div class="searchable-dropdown" id="catalogDropdown">
                                    <button type="button" class="searchable-dropdown-toggle" id="catalogDropdownToggle">
                                        Select Catalog...
                                    </button>
                                    <div class="searchable-dropdown-menu" id="catalogDropdownMenu">
                                        <div class="searchable-dropdown-search">
                                            <input type="text" class="form-control form-control-sm" placeholder="Search catalogs (new ones can be created)" id="catalogSearchInput">
                                        </div>
                                        <div class="searchable-dropdown-items" id="catalogDropdownItems">
                                            <div class="searchable-dropdown-no-results">Loading catalogs... Search to create new ones.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="catalogName" class="form-label" style="color:red;">Catalog Name</label>
                                <input type="text" class="form-control" id="catalogName" placeholder="Select a catalog first" readonly>
                                <div class="invalid-feedback">
                                    Catalog name is required
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="lotSelect" class="form-label" style="color: green;">Lot Number</label>
                                <div class="searchable-dropdown" id="lotDropdown">
                                    <button type="button" class="searchable-dropdown-toggle" id="lotDropdownToggle" disabled>
                                        Select Lot...
                                    </button>
                                    <div class="searchable-dropdown-menu" id="lotDropdownMenu">
                                        <div class="searchable-dropdown-search">
                                            <input type="text" class="form-control form-control-sm" placeholder="Search lots (new ones can be created)" id="lotSearchInput">
                                        </div>
                                        <div class="searchable-dropdown-items" id="lotDropdownItems">
                                            <div class="searchable-dropdown-no-results">Select a catalog first to view/create lots</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Sections Container -->
        <div id="sectionsContainer">
            <!-- Description Section -->
            <div class="section-card" data-section-id="1">
                <div class="section-header">
                    <h5 class="mb-0">Description</h5>
                </div>
                <div class="section-content" id="section_1">
                    <div class="key-values-container" id="keyValues_1">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select a template to view fields
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications Section -->
            <div class="section-card" data-section-id="2">
                <div class="section-header">
                    <h5 class="mb-0">Specifications</h5>
                </div>
                <div class="section-content" id="section_2">
                    <div class="key-values-container" id="keyValues_2">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select a template to view fields
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preparation and Storage Section -->
            <div class="section-card" data-section-id="3">
                <div class="section-header">
                    <h5 class="mb-0">Preparation and Storage</h5>
                </div>
                <div class="section-content" id="section_3">
                    <div class="key-values-container" id="keyValues_3">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select a template to view fields
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3 mb-3">
            <div class="col-12">
                <div class="action-buttons">
                    <button class="btn btn-success" id="saveAllBtn" disabled>
                        <i class="fas fa-save me-1"></i>
                        Save All
                    </button>
                    <button class="btn btn-secondary" id="cancelBtn" disabled>
                        <i class="fas fa-times me-1"></i>
                        Cancel
                    </button>
                    <div class="button-divider"></div>
                    <button class="btn btn-primary" id="previewBtn" disabled>
                        <i class="fas fa-eye me-1"></i>
                        Preview PDF
                    </button>
                    <button class="btn btn-success" id="generateBtn" disabled>
                        <i class="fas fa-file-pdf me-1"></i>
                        Generate PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Catalog Modal -->
    <div class="modal fade" id="createCatalogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Catalog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No matching catalog found in the system. You can create a new catalog by filling in the details below.
                    </div>
                    <div class="mb-3">
                        <label for="newCatalogNumber" class="form-label">Catalog Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newCatalogNumber" placeholder="Enter catalog number (e.g., ABC-123)" required>
                        <div class="form-text">This will be the unique identifier for your catalog</div>
                        <div class="invalid-feedback">
                            Catalog number is required
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="newCatalogName" class="form-label">Catalog Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newCatalogName" placeholder="Enter catalog name" required>
                        <div class="invalid-feedback">
                            Catalog name is required
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmCreateCatalogBtn">Create Catalog</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Lot Modal -->
    <div class="modal fade" id="createLotModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Lot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No matching lot found for this catalog. You can create a new lot by entering the lot number below.
                    </div>
                    <div class="mb-3">
                        <label for="newLotNumber" class="form-label">Lot Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newLotNumber" placeholder="Enter lot number (e.g., LOT-2024-001)" required>
                        <div class="form-text">This will be the unique lot identifier for this catalog</div>
                        <div class="invalid-feedback">
                            Lot number is required
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmCreateLotBtn">Create Lot</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentTemplateCode = null;
        let currentCatalogNumber = null;
        let currentLotNumber = null;
        let templateKeys = {};
        let currentData = {};
        let originalData = {};
        let hasUnsavedChanges = false;
        let catalogsData = [];
        let lotsData = [];
        let searchTimeout = null;
        let originalTemplateCode = null;
        let isChangingTemplate = false;

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing CoA Generator with static templates...');
            initializeSearchableDropdowns();
            loadTemplates();
            loadCatalogs();
            initializeEventListeners();
        });

        // Template radio button handler
        function handleTemplateRadioChange(event) {
            const newTemplateCode = event.target.value;
            
            // Check if changing from original saved template
            if (currentCatalogNumber && originalTemplateCode && originalTemplateCode !== newTemplateCode) {
                const newTemplateName = event.target.nextElementSibling.textContent.trim();
                const originalTemplateName = document.querySelector(`label[for="template_${originalTemplateCode}"]`)?.textContent.trim() || originalTemplateCode;
                
                if (!confirm(`Warning: This catalog is currently using template "${originalTemplateName}".\n\nSwitching to template "${newTemplateName}" will:\n- Create new empty fields\n- DELETE all existing data when you save\n- Update ALL lots to use the new template\n\nAre you sure you want to continue?`)) {
                    // Revert selection
                    const oldRadio = document.getElementById(`template_${originalTemplateCode}`);
                    if (oldRadio) oldRadio.checked = true;
                    return;
                }
                
                // User confirmed template change
                isChangingTemplate = true;
            }
            
            // Check for unsaved changes
            if (hasUnsavedChanges && currentTemplateCode !== newTemplateCode) {
                if (!confirm('You have unsaved changes. Do you want to switch templates and lose your changes?')) {
                    const oldRadio = document.getElementById(`template_${currentTemplateCode}`);
                    if (oldRadio) oldRadio.checked = true;
                    return;
                }
            }
            
            currentTemplateCode = newTemplateCode;
            loadTemplateStructure(newTemplateCode);
        }

        // Load template structure and display fields
        function loadTemplateStructure(templateCode) {
            fetch(`api/get_template_keys.php?template_code=${templateCode}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        templateKeys = {};
                        data.sections.forEach(section => {
                            templateKeys[section.section_id] = {
                                section_name: section.section_name,
                                keys: section.keys.sort((a, b) => a.key_order - b.key_order)
                            };
                        });
                        displayTemplateFields();
                        
                        // If catalog is selected and not changing template, load data
                        if (currentCatalogNumber && !isChangingTemplate) {
                            loadCatalogData();
                        } else if (isChangingTemplate) {
                            // Show empty fields for template change
                            displayEmptyTemplateData();
                            isChangingTemplate = false; // Reset flag
                        }
                    } else {
                        console.error('Failed to load template structure:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading template structure:', error);
                });
        }

        // Display empty template data when switching templates
        function displayEmptyTemplateData() {
            // Enable all textareas
            enableAllTextareas();
            
            // Clear all field values
            document.querySelectorAll('.bulk-edit-textarea').forEach(textarea => {
                textarea.value = '';
            });
            
            // Reset data tracking
            currentData = {};
            originalData = {};
            hasUnsavedChanges = true; // Mark as having changes since fields are empty
            
            // Update buttons
            updateButtonStates();
            
            // Update save button to indicate unsaved changes
            const saveBtn = document.getElementById('saveAllBtn');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All*';
            }
        }

        // Display template fields with disabled textareas
        function displayTemplateFields() {
            const sections = [1, 2, 3];
            
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = '';
                
                if (templateKeys[sectionId] && templateKeys[sectionId].keys.length > 0) {
                    templateKeys[sectionId].keys.forEach(key => {
                        const keyName = key.key_name;
                        const keySource = key.key_source;
                        const sourceColor = keySource === 'catalog' ? 'red' : 'green';
                        
                        const kvHtml = `
                            <div class="key-value-row" data-key="${keyName}" data-source="${keySource}">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <label class="form-label" style="color: ${sourceColor};">${keyName}</label>
                                    </div>
                                    <div class="col-md-8">
                                        <textarea class="form-control bulk-edit-textarea" 
                                                  id="textarea_${sectionId}_${keyName.replace(/\s+/g, '_')}" 
                                                  rows="2" 
                                                  style="height: 38px;" 
                                                  placeholder="Enter ${keyName.toLowerCase()}..."
                                                  disabled></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.insertAdjacentHTML('beforeend', kvHtml);
                    });
                } else {
                    container.innerHTML = `
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            No keys defined in template for this section
                        </div>
                    `;
                }
            });
        }

        // Load catalog data when catalog is selected
        function loadCatalogData() {
            if (!currentTemplateCode || !currentCatalogNumber) return;
            
            disableAllTextareas();
            
            fetch(`api/get_section_data.php?catalog_number=${currentCatalogNumber}&template_code=${currentTemplateCode}&lot_number=${currentLotNumber || ''}`)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        populateFieldsWithData(data.sections_data);
                        enableAllTextareas();
                        updateButtonStates();
                    } else {
                        console.error('Error loading data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading catalog data:', error);
                });
        }

        // Populate fields with loaded data
        function populateFieldsWithData(sectionsData) {
            currentData = {};
            originalData = {};
            
            sectionsData.forEach(section => {
                const sectionId = section.section_id;
                currentData[sectionId] = {};
                originalData[sectionId] = {};
                
                section.key_values.forEach(kv => {
                    const textarea = document.getElementById(`textarea_${sectionId}_${kv.key.replace(/\s+/g, '_')}`);
                    if (textarea) {
                        textarea.value = kv.value || '';
                        currentData[sectionId][kv.key] = kv.value || '';
                        originalData[sectionId][kv.key] = kv.value || '';
                    }
                });
            });
            
            hasUnsavedChanges = false;
            
            // Clear any validation errors
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            document.querySelectorAll('.border-warning').forEach(element => {
                element.classList.remove('border-warning');
            });
            
            // Reset save button text
            const saveBtn = document.getElementById('saveAllBtn');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All';
            }
            
            // Check if any fields are empty - if so, enable save button
            let hasEmptyFields = false;
            document.querySelectorAll('.bulk-edit-textarea:not(:disabled)').forEach(textarea => {
                if (!textarea.value.trim()) {
                    hasEmptyFields = true;
                }
            });
            
            if (hasEmptyFields) {
                hasUnsavedChanges = true; // Allow saving empty fields
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All*';
                }
            }
            
            // Update button states after loading data
            updateButtonStates();
        }

        // Enable/disable textareas
        function enableAllTextareas() {
            document.querySelectorAll('.bulk-edit-textarea').forEach(textarea => {
                textarea.disabled = false;
            });
        }

        function disableAllTextareas() {
            document.querySelectorAll('.bulk-edit-textarea').forEach(textarea => {
                textarea.disabled = true;
            });
        }

        // Update button states
        function updateButtonStates() {
            const hasTemplate = !!currentTemplateCode;
            const hasCatalog = !!currentCatalogNumber;
            const hasLot = !!currentLotNumber;
            const canInteract = hasTemplate && hasCatalog && hasLot;
            
            // Save and Cancel buttons need both interaction ability AND unsaved changes
            const canSaveOrCancel = canInteract && hasUnsavedChanges;
            document.getElementById('saveAllBtn').disabled = !canSaveOrCancel;
            document.getElementById('cancelBtn').disabled = !canSaveOrCancel;
            
            // Preview and Generate buttons only need interaction ability
            document.getElementById('previewBtn').disabled = !canInteract;
            document.getElementById('generateBtn').disabled = !canInteract;
            
            // Lot dropdown state
            const lotToggle = document.getElementById('lotDropdownToggle');
            if (lotToggle) {
                lotToggle.disabled = !hasCatalog;
            }
        }

        // New function to check if all fields are filled
        function checkAllFieldsFilled() {
            let allFilled = true;
            
            // Check catalog name
            const catalogName = document.getElementById('catalogName').value.trim();
            if (!catalogName) {
                allFilled = false;
            }
            
            // Check all textareas that are not disabled
            document.querySelectorAll('.bulk-edit-textarea:not(:disabled)').forEach(textarea => {
                if (!textarea.value.trim()) {
                    allFilled = false;
                }
            });
            
            return allFilled;
        }

        // Track changes
        function markFieldAsChanged(element) {
            element.classList.add('border-warning');
            hasUnsavedChanges = true;
            
            // Update save button to show unsaved indicator
            const saveBtn = document.getElementById('saveAllBtn');
            if (saveBtn && !saveBtn.innerHTML.includes('*')) {
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All*';
            }
            
            // Update button states
            updateButtonStates();
        }

        // Validate all fields
        function validateAllFields() {
            let isValid = true;
            let firstInvalidField = null;
            
            // Check all textareas that are not disabled
            document.querySelectorAll('.bulk-edit-textarea:not(:disabled)').forEach(textarea => {
                const value = textarea.value.trim();
                if (!value) {
                    textarea.classList.add('is-invalid');
                    const feedback = textarea.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = 'This field is required';
                    }
                    isValid = false;
                    
                    // Track first invalid field
                    if (!firstInvalidField) {
                        firstInvalidField = textarea;
                    }
                } else {
                    textarea.classList.remove('is-invalid');
                    const feedback = textarea.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = '';
                    }
                }
            });
            
            // Validate catalog name
            const catalogName = document.getElementById('catalogName');
            if (!catalogName.value.trim()) {
                catalogName.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = catalogName;
                }
            } else {
                catalogName.classList.remove('is-invalid');
            }
            
            // Focus on first invalid field
            if (firstInvalidField) {
                firstInvalidField.focus();
            }
            
            return isValid;
        }

        // Save all data
        function saveAllData() {
            // Validate all required fields
            if (!currentTemplateCode) {
                alert('Please select a template first');
                return;
            }
            
            if (!currentCatalogNumber) {
                alert('Please select a catalog');
                return;
            }
            
            if (!currentLotNumber) {
                alert('Please select a lot number');
                return;
            }
            
            const catalogName = document.getElementById('catalogName').value.trim();
            if (!catalogName) {
                alert('Catalog name cannot be empty');
                document.getElementById('catalogName').focus();
                return;
            }
            
            // Validate all textareas have values
            if (!validateAllFields()) {
                alert('Please fill in all required fields before saving.');
                return;
            }
            
            // Check if any modal is open
            const modalsOpen = document.querySelector('.modal.show');
            if (modalsOpen) {
                alert('Please complete the current action before saving');
                return;
            }
            
            // Show loading state
            const saveBtn = document.getElementById('saveAllBtn');
            const originalBtnHtml = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
            saveBtn.disabled = true;
            
            // Collect all key-value pairs
            const keyValues = [];
            const sections = [1, 2, 3];
            
            sections.forEach(sectionId => {
                if (templateKeys[sectionId] && templateKeys[sectionId].keys) {
                    templateKeys[sectionId].keys.forEach(key => {
                        const keyName = key.key_name;
                        const keySource = key.key_source;
                        const textareaId = `textarea_${sectionId}_${keyName.replace(/\s+/g, '_')}`;
                        const textarea = document.getElementById(textareaId);
                        
                        if (textarea) {
                            const value = textarea.value.trim();
                            keyValues.push({
                                key: keyName,
                                value: value,
                                source: keySource
                            });
                        }
                    });
                }
            });
            
            // Prepare payload
            const payload = {
                catalog_number: currentCatalogNumber,
                catalog_name: catalogName,
                lot_number: currentLotNumber,
                template_code: currentTemplateCode,
                key_values: keyValues
            };
            
            // Call bulk save API
            fetch('api/save_all_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success feedback
                    saveBtn.innerHTML = '<i class="fas fa-check me-1"></i> Saved!';
                    saveBtn.classList.remove('btn-success');
                    saveBtn.classList.add('btn-outline-success');
                    
                    // Update catalog name field to reflect saved state
                    const catalogNameField = document.getElementById('catalogName');
                    catalogNameField.classList.remove('border-warning');
                    
                    // Remove warning borders from all textareas
                    document.querySelectorAll('.bulk-edit-textarea').forEach(textarea => {
                        textarea.classList.remove('border-warning');
                    });
                    
                    // Reset hasUnsavedChanges flag
                    hasUnsavedChanges = false;
                    
                    // Update button states
                    updateButtonStates();
                    
                    // Update original data to current values
                    sections.forEach(sectionId => {
                        originalData[sectionId] = {};
                        if (templateKeys[sectionId] && templateKeys[sectionId].keys) {
                            templateKeys[sectionId].keys.forEach(key => {
                                const keyName = key.key_name;
                                const textareaId = `textarea_${sectionId}_${keyName.replace(/\s+/g, '_')}`;
                                const textarea = document.getElementById(textareaId);
                                if (textarea) {
                                    originalData[sectionId][keyName] = textarea.value.trim();
                                }
                            });
                        }
                    });
                    
                    console.log('Save stats:', data.stats);
                    
                    // Restore button after 2 seconds
                    setTimeout(() => {
                        saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All';
                        saveBtn.classList.add('btn-success');
                        saveBtn.classList.remove('btn-outline-success');
                        saveBtn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Failed to save data');
                }
            })
            .catch(error => {
                console.error('Error saving data:', error);
                alert('Error saving data: ' + error.message);
                saveBtn.innerHTML = originalBtnHtml;
                saveBtn.disabled = false;
            });
        }

        // Cancel changes
        function cancelChanges() {
            if (!hasUnsavedChanges) {
                return; // Nothing to cancel
            }
            
            if (!confirm('Are you sure you want to cancel all changes? All unsaved data will be lost.')) {
                return;
            }
            
            // Restore original values
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                if (originalData[sectionId]) {
                    Object.keys(originalData[sectionId]).forEach(key => {
                        const textareaId = `textarea_${sectionId}_${key.replace(/\s+/g, '_')}`;
                        const textarea = document.getElementById(textareaId);
                        if (textarea) {
                            textarea.value = originalData[sectionId][key];
                            textarea.classList.remove('is-invalid', 'border-warning');
                            const feedback = textarea.nextElementSibling;
                            if (feedback && feedback.classList.contains('invalid-feedback')) {
                                feedback.textContent = '';
                            }
                        }
                    });
                }
            });
            
            // Restore catalog name if it was changed
            const catalogNameField = document.getElementById('catalogName');
            if (catalogNameField) {
                // Reload the original catalog name from the selected catalog
                const selectedCatalog = catalogsData.find(cat => cat.id == currentCatalogId);
                if (selectedCatalog) {
                    catalogNameField.value = selectedCatalog.catalog_name || '';
                }
                catalogNameField.classList.remove('is-invalid', 'border-warning');
            }
            
            hasUnsavedChanges = false;
            
            // Reset save button text
            const saveBtn = document.getElementById('saveAllBtn');
            if (saveBtn) {
                saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All';
            }
            
            // Check if any fields are empty after restore - if so, keep save enabled
            let hasEmptyFields = false;
            document.querySelectorAll('.bulk-edit-textarea:not(:disabled)').forEach(textarea => {
                if (!textarea.value.trim()) {
                    hasEmptyFields = true;
                }
            });
            
            if (hasEmptyFields) {
                hasUnsavedChanges = true;
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All*';
                }
            }
            
            // Update button states
            updateButtonStates();
            
            console.log('All changes cancelled, data restored to original state');
        }

        // Event listeners
        function initializeEventListeners() {
            // Action buttons
            document.getElementById('saveAllBtn').addEventListener('click', saveAllData);
            document.getElementById('cancelBtn').addEventListener('click', cancelChanges);
            document.getElementById('previewBtn').addEventListener('click', previewPDF);
            document.getElementById('generateBtn').addEventListener('click', generatePDF);
            
            // Catalog name change tracking
            document.getElementById('catalogName').addEventListener('input', function() {
                markFieldAsChanged(this);
                this.classList.remove('is-invalid'); // Remove error on input
            });
            
            // Track changes in textareas (using event delegation)
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('bulk-edit-textarea')) {
                    markFieldAsChanged(e.target);
                    e.target.classList.remove('is-invalid'); // Remove error on input
                }
            });
            
            // Prevent Enter key from submitting in textareas
            document.addEventListener('keypress', function(e) {
                if (e.target.classList.contains('bulk-edit-textarea') && e.key === 'Enter') {
                    e.preventDefault();
                    // Allow Shift+Enter for new lines
                    if (e.shiftKey) {
                        const textarea = e.target;
                        const start = textarea.selectionStart;
                        const end = textarea.selectionEnd;
                        const value = textarea.value;
                        textarea.value = value.substring(0, start) + '\n' + value.substring(end);
                        textarea.selectionStart = textarea.selectionEnd = start + 1;
                        markFieldAsChanged(textarea);
                    }
                }
            });
            
            // Create catalog/lot modal buttons
            document.getElementById('confirmCreateCatalogBtn').addEventListener('click', createNewCatalog);
            document.getElementById('confirmCreateLotBtn').addEventListener('click', createNewLot);
            
            // Enter key support in modal inputs
            document.getElementById('newCatalogNumber').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('newCatalogName').focus();
                }
            });
            
            document.getElementById('newCatalogName').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    createNewCatalog();
                }
            });
            
            document.getElementById('newLotNumber').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    createNewLot();
                }
            });
            
            // Warn about unsaved changes when leaving page
            window.addEventListener('beforeunload', function(e) {
                if (hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
                    return e.returnValue;
                }
            });
        }

        // Searchable dropdown functions
        function initializeSearchableDropdowns() {
            initializeCatalogDropdown();
            initializeLotDropdown();
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.searchable-dropdown')) {
                    closeAllDropdowns();
                }
            });
        }

        function initializeCatalogDropdown() {
            const toggle = document.getElementById('catalogDropdownToggle');
            const search = document.getElementById('catalogSearchInput');
            
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    toggleDropdown('catalog');
                });
            }
            
            if (search) {
                search.addEventListener('input', function() {
                    handleCatalogSearch(this.value);
                });
                search.addEventListener('click', e => e.stopPropagation());
            }
        }

        function initializeLotDropdown() {
            const toggle = document.getElementById('lotDropdownToggle');
            const search = document.getElementById('lotSearchInput');
            
            if (toggle) {
                toggle.addEventListener('click', function(e) {
                    if (!this.disabled) {
                        e.stopPropagation();
                        toggleDropdown('lot');
                    }
                });
            }
            
            if (search) {
                search.addEventListener('input', function() {
                    handleLotSearch(this.value);
                });
                search.addEventListener('click', e => e.stopPropagation());
            }
        }

        // Smart search handlers
        function handleCatalogSearch(searchTerm) {
            // Clear previous timeout
            if (searchTimeout) clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                filterCatalogItems(searchTerm);
                
                // If search term exists and no results found, show create option
                if (searchTerm.trim() && !hasVisibleCatalogItems()) {
                    showCreateCatalogOption(searchTerm.trim());
                }
            }, 300); // Debounce for 300ms
        }

        function handleLotSearch(searchTerm) {
            // Clear previous timeout
            if (searchTimeout) clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                filterLotItems(searchTerm);
                
                // If search term exists and no results found, show create option
                if (searchTerm.trim() && !hasVisibleLotItems()) {
                    showCreateLotOption(searchTerm.trim());
                }
            }, 300); // Debounce for 300ms
        }

        function filterCatalogItems(searchTerm) {
            const container = document.getElementById('catalogDropdownItems');
            const items = container.querySelectorAll('.searchable-dropdown-item');
            const searchLower = searchTerm.toLowerCase();
            let hasVisibleItems = false;
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchLower)) {
                    item.classList.remove('hidden');
                    hasVisibleItems = true;
                } else {
                    item.classList.add('hidden');
                }
            });
            
            return hasVisibleItems;
        }

        function filterLotItems(searchTerm) {
            const container = document.getElementById('lotDropdownItems');
            const items = container.querySelectorAll('.searchable-dropdown-item');
            const searchLower = searchTerm.toLowerCase();
            let hasVisibleItems = false;
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchLower)) {
                    item.classList.remove('hidden');
                    hasVisibleItems = true;
                } else {
                    item.classList.add('hidden');
                }
            });
            
            return hasVisibleItems;
        }

        function hasVisibleCatalogItems() {
            const container = document.getElementById('catalogDropdownItems');
            const visibleItems = container.querySelectorAll('.searchable-dropdown-item:not(.hidden)');
            return visibleItems.length > 0;
        }

        function hasVisibleLotItems() {
            const container = document.getElementById('lotDropdownItems');
            const visibleItems = container.querySelectorAll('.searchable-dropdown-item:not(.hidden)');
            return visibleItems.length > 0;
        }

        function showCreateCatalogOption(searchTerm) {
            // Show modal for creating new catalog
            // Pre-fill with search term but allow user to change it
            document.getElementById('newCatalogNumber').value = searchTerm;
            document.getElementById('newCatalogName').value = '';
            document.getElementById('newCatalogNumber').classList.remove('is-invalid');
            document.getElementById('newCatalogName').classList.remove('is-invalid');
            
            // Close dropdown
            closeAllDropdowns();
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('createCatalogModal'));
            modal.show();
            
            // Focus on catalog number input when modal is shown
            document.getElementById('createCatalogModal').addEventListener('shown.bs.modal', function() {
                document.getElementById('newCatalogNumber').focus();
                // Select all text for easy replacement
                document.getElementById('newCatalogNumber').select();
            }, { once: true });
        }

        function showCreateLotOption(searchTerm) {
            // Show modal for creating new lot
            // Pre-fill with search term but allow user to change it
            document.getElementById('newLotNumber').value = searchTerm;
            document.getElementById('newLotNumber').classList.remove('is-invalid');
            
            // Close dropdown
            closeAllDropdowns();
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('createLotModal'));
            modal.show();
            
            // Focus on lot number input when modal is shown
            document.getElementById('createLotModal').addEventListener('shown.bs.modal', function() {
                document.getElementById('newLotNumber').focus();
                // Select all text for easy replacement
                document.getElementById('newLotNumber').select();
            }, { once: true });
        }

        function createNewCatalog() {
            const catalogNumber = document.getElementById('newCatalogNumber').value.trim();
            const catalogName = document.getElementById('newCatalogName').value.trim();
            
            let isValid = true;
            
            if (!catalogNumber) {
                document.getElementById('newCatalogNumber').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('newCatalogNumber').classList.remove('is-invalid');
                
                // Check if catalog number already exists
                const existingCatalog = catalogsData.find(cat => cat.catalog_number.toLowerCase() === catalogNumber.toLowerCase());
                if (existingCatalog) {
                    document.getElementById('newCatalogNumber').classList.add('is-invalid');
                    const feedback = document.getElementById('newCatalogNumber').nextElementSibling.nextElementSibling;
                    feedback.textContent = 'This catalog number already exists';
                    isValid = false;
                }
            }
            
            if (!catalogName) {
                document.getElementById('newCatalogName').classList.add('is-invalid');
                isValid = false;
            } else {
                document.getElementById('newCatalogName').classList.remove('is-invalid');
            }
            
            if (!isValid) return;
            
            // Get current template code
            if (!currentTemplateCode) {
                alert('Please select a template first');
                return;
            }
            
            // Create payload
            const payload = {
                catalog_number: catalogNumber,
                catalog_name: catalogName,
                template_code: currentTemplateCode  // Changed from template_id
            };
            
            // Call API to create catalog
            fetch('api/save_catalog.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('createCatalogModal')).hide();
                    
                    // Clear search input
                    document.getElementById('catalogSearchInput').value = '';
                    
                    // Reload catalogs
                    loadCatalogs();
                    
                    // After catalogs are loaded, select the new one
                    setTimeout(() => {
                        // Just need to select by catalog number now
                        const itemsContainer = document.getElementById('catalogDropdownItems');
                        const items = itemsContainer.querySelectorAll('.searchable-dropdown-item');
                        items.forEach(item => {
                            if (item.textContent === catalogNumber) {
                                item.click();
                            }
                        });
                    }, 500);
                    
                    console.log('Catalog created successfully');
                } else {
                    alert('Error creating catalog: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error creating catalog:', error);
                alert('Error creating catalog. Please try again.');
            });
        }

        function createNewLot() {
            const lotNumber = document.getElementById('newLotNumber').value.trim();
            
            if (!lotNumber) {
                document.getElementById('newLotNumber').classList.add('is-invalid');
                return;
            } else {
                document.getElementById('newLotNumber').classList.remove('is-invalid');
                
                // Check if lot number already exists
                const existingLot = lotsData.find(lot => lot.lot_number.toLowerCase() === lotNumber.toLowerCase());
                if (existingLot) {
                    document.getElementById('newLotNumber').classList.add('is-invalid');
                    const feedback = document.getElementById('newLotNumber').nextElementSibling.nextElementSibling;
                    feedback.textContent = 'This lot number already exists for this catalog';
                    return;
                }
            }
            
            if (!currentCatalogNumber) {
                alert('Please select a catalog first');
                return;
            }

            const payload = {
                catalog_number: currentCatalogNumber,
                lot_number: lotNumber
            };
            
            // Call API to create lot
            fetch('api/save_lot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('createLotModal')).hide();
                    
                    // Clear search input
                    document.getElementById('lotSearchInput').value = '';
                    
                    // Reload lots
                    loadLots(currentCatalogNumber);
                    
                    // After lots are loaded, select the new one
                    setTimeout(() => {
                        selectLot(lotNumber);
                    }, 500);
                    
                    console.log('Lot created successfully');
                } else {
                    alert('Error creating lot: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error creating lot:', error);
                alert('Error creating lot. Please try again.');
            });
        }

        function toggleDropdown(type) {
            const menu = document.getElementById(`${type}DropdownMenu`);
            const search = document.getElementById(`${type}SearchInput`);
            const isOpen = menu.classList.contains('show');
            
            closeAllDropdowns();
            
            if (!isOpen) {
                menu.classList.add('show');
                setTimeout(() => {
                    search.focus();
                    search.value = '';
                    if (type === 'catalog') {
                        filterCatalogItems('');
                    } else {
                        filterLotItems('');
                    }
                }, 100);
            }
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.searchable-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        function selectCatalog(catalogNumber, catalogName) {
            const toggle = document.getElementById('catalogDropdownToggle');
            toggle.textContent = catalogNumber;
            
            currentCatalogNumber = catalogNumber;
            
            // Update catalog name field
            const catalogNameField = document.getElementById('catalogName');
            catalogNameField.value = catalogName || '';
            catalogNameField.readOnly = false; // Allow editing catalog name
            
            closeAllDropdowns();
            
            // Check if catalog has existing template/data
            fetch(`api/get_catalog_template.php?catalog_number=${catalogNumber}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.template_code) {
                        // Auto-select the catalog's template
                        const templateRadio = document.getElementById(`template_${data.template_code}`);
                        if (templateRadio) {
                            templateRadio.checked = true;
                            currentTemplateCode = data.template_code;
                            originalTemplateCode = data.template_code; // Store original
                            
                            // Load template structure first
                            return loadTemplateStructure(data.template_code);
                        }
                    } else {
                        // No existing data, keep current template selection
                        originalTemplateCode = null;
                    }
                })
                .then(() => {
                    // Load lots after template is set
                    loadLots(catalogNumber);
                })
                .catch(error => {
                    console.error('Error checking catalog template:', error);
                    originalTemplateCode = null;
                    loadLots(catalogNumber);
                });
            
            updateButtonStates();
        }

        function selectLot(lotNumber) {
            const toggle = document.getElementById('lotDropdownToggle');
            toggle.textContent = lotNumber;
            
            currentLotNumber = lotNumber;
            
            closeAllDropdowns();
            
            // Load lot data if template and catalog are selected
            if (currentTemplateCode && currentCatalogNumber) {
                loadCatalogData();
            }
            
            updateButtonStates();
        }

        function populateCatalogDropdown(catalogs) {
            const container = document.getElementById('catalogDropdownItems');
            container.innerHTML = '';
            
            if (catalogs.length === 0) {
                container.innerHTML = '<div class="searchable-dropdown-no-results">No catalogs found. Search above to create one.</div>';
            } else {
                catalogs.forEach(catalog => {
                    const item = document.createElement('div');
                    item.className = 'searchable-dropdown-item';
                    item.textContent = catalog.catalog_number;
                    item.onclick = () => selectCatalog(
                        catalog.catalog_number, 
                        catalog.catalog_name
                    );
                    container.appendChild(item);
                });
            }
        }

        function populateLotDropdown(lots) {
            const container = document.getElementById('lotDropdownItems');
            container.innerHTML = '';
            
            if (lots.length === 0) {
                container.innerHTML = '<div class="searchable-dropdown-no-results">No lots found. Search above to create one.</div>';
            } else {
                lots.forEach(lot => {
                    const item = document.createElement('div');
                    item.className = 'searchable-dropdown-item';
                    item.textContent = lot.lot_number;
                    item.onclick = () => selectLot(lot.lot_number);
                    container.appendChild(item);
                });
            }
        }

        // Load functions
        function loadTemplates() {
            fetch('api/get_templates.php')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('templateRadioButtons');
                    container.innerHTML = '';
                    
                    if (data.success && data.templates && data.templates.length > 0) {
                        const radioGroup = document.createElement('div');
                        radioGroup.className = 'template-radio-group';
                        
                        data.templates.forEach((template, index) => {
                            const radioDiv = document.createElement('div');
                            radioDiv.className = 'template-radio';
                            
                            const radioInput = document.createElement('input');
                            radioInput.type = 'radio';
                            radioInput.name = 'templateRadio';
                            radioInput.id = `template_${template.template_code}`;
                            radioInput.value = template.template_code;
                            radioInput.className = 'form-check-input';
                            radioInput.addEventListener('change', handleTemplateRadioChange);
                            
                            const radioLabel = document.createElement('label');
                            radioLabel.htmlFor = `template_${template.template_code}`;
                            radioLabel.textContent = template.template_name;
                            
                            // First template is default
                            if (index === 0) {
                                radioInput.checked = true;
                                currentTemplateCode = template.template_code;
                                loadTemplateStructure(template.template_code);
                            }
                            
                            radioDiv.appendChild(radioInput);
                            radioDiv.appendChild(radioLabel);
                            radioGroup.appendChild(radioDiv);
                        });
                        
                        container.appendChild(radioGroup);
                    } else {
                        container.innerHTML = '<div class="text-muted">No templates found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading templates:', error);
                });
        }

        function loadCatalogs() {
            fetch('api/get_catalog_data.php')
                .then(response => response.json())
                .then(data => {
                    catalogsData = Array.isArray(data) ? data : [];
                    populateCatalogDropdown(catalogsData);
                })
                .catch(error => {
                    console.error('Error loading catalogs:', error);
                });
        }

        function loadLots(catalogNumber) {
            fetch(`api/get_lot_data.php?catalog_number=${catalogNumber}`)
                .then(response => response.json())
                .then(data => {
                    lotsData = Array.isArray(data) ? data : [];
                    populateLotDropdown(lotsData);
                    
                    // Enable lot dropdown
                    const lotToggle = document.getElementById('lotDropdownToggle');
                    if (lotToggle) {
                        lotToggle.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error loading lots:', error);
                });
        }

        // PDF functions
        function previewPDF() {
            if (!currentCatalogNumber || !currentLotNumber) {
                alert('Please select both catalog and lot number');
                return;
            }
            
            // Double-check no unsaved changes
            if (hasUnsavedChanges) {
                alert('Please save all changes before previewing the PDF');
                return;
            }
            
            // Double-check all fields are filled
            if (!checkAllFieldsFilled()) {
                alert('Please fill all required fields before previewing the PDF');
                return;
            }
            
            // Show loading state on button
            const previewBtn = document.getElementById('previewBtn');
            const originalText = previewBtn.innerHTML;
            previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
            previewBtn.disabled = true;
            
            // Build URL for preview API
            const previewUrl = `api/preview_pdf.php?catalog_number=${encodeURIComponent(currentCatalogNumber)}&lot_number=${encodeURIComponent(currentLotNumber)}`;
            
            // Open PDF in new window
            const previewWindow = window.open(previewUrl, '_blank');
            
            // Check if popup was blocked
            if (!previewWindow || previewWindow.closed || typeof previewWindow.closed == 'undefined') {
                alert('Please allow popups for PDF preview');
            }
            
            // Restore button after short delay
            setTimeout(() => {
                previewBtn.innerHTML = originalText;
                previewBtn.disabled = false;
                updateButtonStates();
            }, 1000);
        }

        // Generate (Download) PDF - Updated version
        function generatePDF() {
            if (!currentCatalogNumber || !currentLotNumber) {
                alert('Please select both catalog and lot number');
                return;
            }
            
            // Double-check no unsaved changes
            if (hasUnsavedChanges) {
                alert('Please save all changes before generating the PDF');
                return;
            }
            
            // Double-check all fields are filled
            if (!checkAllFieldsFilled()) {
                alert('Please fill all required fields before generating the PDF');
                return;
            }
            
            // Show loading state on button
            const generateBtn = document.getElementById('generateBtn');
            const originalText = generateBtn.innerHTML;
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
            generateBtn.disabled = true;
            
            // Build URL for generate API
            const generateUrl = `api/generate_pdf.php?catalog_number=${encodeURIComponent(currentCatalogNumber)}&lot_number=${encodeURIComponent(currentLotNumber)}`;
            
            // Method 1: Try using fetch first (more reliable)
            fetch(generateUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('PDF generation failed');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Create a download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `CoA_${currentCatalogNumber}_${currentLotNumber}_${new Date().toISOString().slice(0,10).replace(/-/g,'')}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    
                    // Cleanup
                    setTimeout(() => {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }, 100);
                    
                    // Show success
                    showPDFGeneratedSuccess();
                    
                    // Log success
                    console.log('PDF generated successfully');
                })
                .catch(error => {
                    console.error('PDF generation error:', error);
                    
                    // Method 2: Fallback to direct window.open
                    console.log('Trying fallback method...');
                    window.open(generateUrl, '_blank');
                    
                    // Still show success message (optimistic)
                    setTimeout(() => {
                        showPDFGeneratedSuccess();
                    }, 1000);
                })
                .finally(() => {
                    // Restore button
                    setTimeout(() => {
                        generateBtn.innerHTML = originalText;
                        generateBtn.disabled = false;
                        updateButtonStates();
                    }, 1500);
                });
        }

        // Show success message after PDF generation
        function showPDFGeneratedSuccess() {
            // Create a temporary success alert
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>Success!</strong> PDF has been generated and downloaded.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>