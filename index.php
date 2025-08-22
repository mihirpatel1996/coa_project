<?php include './includes/header.php'?>

<body>
    <!-- Include navbar -->
    <?php include './includes/navbar.php'?>
    
    <div class="container mt-2">
        <!-- Selection Form -->
        <div class="row mb-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Template Selection with Radio Buttons -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Template
                                    </label>
                                    <a href="view_pdfs.php" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i>
                                        View Generated PDFs
                                    </a>
                                </div>
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
                    <h6 class="mb-0">Description</h6>
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
                    <h6 class="mb-0">Specifications</h6>
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
                    <h6 class="mb-0">Preparation and Storage</h6>
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
                    <button class="btn btn-info" id="bulkUploadBtn">
                        <i class="fas fa-upload me-1"></i>
                        Bulk Upload
                    </button>
                    <!-- <div class="button-divider"></div> -->
                    <button class="btn btn-success" id="saveAllBtn" disabled>
                        <i class="fas fa-save me-1"></i>
                        Save All
                    </button>

                    <button class="btn btn-primary" id="previewBtn" disabled>
                        <i class="fas fa-eye me-1"></i>
                        Preview PDF
                    </button>
                    <button class="btn btn-success" id="generateBtn" disabled>
                        <i class="fas fa-file-pdf me-1"></i>
                        Generate PDF
                    </button>
                    <!-- <div class="button-divider"></div> -->

                    <button class="btn btn-secondary" id="cancelBtn" disabled>
                        <i class="fas fa-times me-1"></i>
                        Cancel
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
                    <h6 class="modal-title">Create New Catalog</h6>
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
                    <h6 class="modal-title">Create New Lot</h6>
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

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h5 class="modal-title">
                        <i class="fas fa-upload me-2"></i>
                        Bulk Upload - Catalogs & Lots
                    </h5>
                    <button class="btn btn-outline-primary ms-3" id="downloadTemplatesBtn">
                        <i class="fas fa-download me-2"></i>
                        Download Templates
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- <div class="row"> -->
                        <!-- Catalog Upload Section -->
                        <div class="card mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-book me-2"></i>
                                        Catalog Upload
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Upload Catalog Excel</label>
                                        <input type="file" class="form-control catalog-file-input" id="catalogExcelInput" accept=".xlsx,.xls">
                                        <div class="form-text">
                                            Maximum file size: 10MB. Maximum rows: 5,000.
                                        </div>
                                    </div>
                                    <button class="btn btn-primary" id="uploadCatalogBtn" disabled>
                                        <i class="fas fa-upload me-2"></i>
                                        Upload Catalogs
                                    </button>
                                    
                                    <!-- Catalog Progress -->
                                    <div id="catalogUploadProgress" class="mt-3" style="display: none;">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm text-primary mb-2" role="status">
                                                <span class="visually-hidden">Processing...</span>
                                            </div>
                                            <p class="mb-0 text-muted">Processing catalogs...</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Catalog Results -->
                                    <div id="catalogResults" class="mt-3" style="display: none;">
                                        <!-- Catalog Success Alert -->
                                        <div id="catalogSuccessAlert" class="alert alert-success alert-sm" style="display: none;">
                                            <small>
                                                <i class="fas fa-check-circle me-1"></i>
                                                <span id="catalogSuccessMessage"></span>
                                            </small>
                                        </div>
                                        
                                        <!-- Catalog Error Alert -->
                                        <div id="catalogErrorAlert" class="alert alert-danger alert-sm" style="display: none;">
                                            <small>
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <span id="catalogErrorMessage"></span>
                                            </small>
                                        </div>
                                        
                                        <!-- Catalog Summary -->
                                        <div id="catalogSummaryContainer" style="display: none; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.9rem;">
                                            <strong>Summary:</strong>
                                            <div id="catalogSummaryText"></div>
                                            <div class="mt-2">
                                                <!-- Download Complete Report -->
                                                <div id="catalogCompleteSection" class="mb-2" style="display: none;">
                                                    <button class="btn btn-primary btn-sm" id="downloadCatalogCompleteBtn">
                                                        <i class="fas fa-download me-1"></i>
                                                        Upload Report
                                                    </button>
                                                </div>
                                                <!-- Download Catalog Updated Report -->
                                                <div id="catalogUpdatedSection" class="mt-2" style="display: none;">
                                                    <button class="btn btn-info btn-sm" id="downloadCatalogUpdatedBtn">
                                                        <i class="fas fa-download me-1"></i>
                                                        Updated Records Report
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Lot Upload Section -->
                        <div class="card mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-tag me-2"></i>
                                        Lot Upload
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Upload Lot Excel</label>
                                        <input type="file" class="form-control lot-file-input" id="lotExcelInput" accept=".xlsx,.xls">
                                        <div class="form-text">
                                            Maximum file size: 10MB. Maximum rows: 5,000.
                                        </div>
                                    </div>
                                    <button class="btn btn-success" id="uploadLotBtn" disabled>
                                        <i class="fas fa-upload me-2"></i>
                                        Upload Lots
                                    </button>
                                    
                                    <!-- Lot Progress -->
                                    <div id="lotUploadProgress" class="mt-3" style="display: none;">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm text-success mb-2" role="status">
                                                <span class="visually-hidden">Processing...</span>
                                            </div>
                                            <p class="mb-0 text-muted">Processing lots...</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Lot Results -->
                                    <div id="lotResults" class="mt-3" style="display: none;">
                                        <!-- Lot Success Alert -->
                                        <div id="lotSuccessAlert" class="alert alert-success alert-sm" style="display: none;">
                                            <small>
                                                <i class="fas fa-check-circle me-1"></i>
                                                <span id="lotSuccessMessage"></span>
                                            </small>
                                        </div>
                                        
                                        <!-- Lot Error Alert -->
                                        <div id="lotErrorAlert" class="alert alert-danger alert-sm" style="display: none;">
                                            <small>
                                                <i class="fas fa-exclamation-circle me-1"></i>
                                                <span id="lotErrorMessage"></span>
                                            </small>
                                        </div>
                                        
                                        <!-- Lot Summary -->
                                        <div id="lotSummaryContainer" style="display: none; background-color: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 0.9rem;">
                                            <strong>Summary:</strong>
                                            <div id="lotSummaryText"></div>
                                            <div class="mt-2">
                                                <div id="lotCompleteSection" class="mb-2" style="display: none;">
                                                    <button class="btn btn-primary btn-sm" id="downloadLotCompleteBtn">
                                                        <i class="fas fa-download me-1"></i>
                                                        Upload Report
                                                    </button>
                                                </div>
                                                <!-- Download Lot Updated Report -->
                                                <div id="lotUpdatedSection" class="mt-2" style="display: none;">
                                                    <button class="btn btn-info btn-sm" id="downloadLotUpdatedBtn">
                                                        <i class="fas fa-download me-1"></i>
                                                        Updated Records Report
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- </div> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
            // Initialize bulk upload
            initializeBulkUpload();
        });

        function resetCatalogAndLotSelection() {
            // Reset global state variables
            currentCatalogNumber = null;
            currentLotNumber = null;
            originalTemplateCode = null;
            hasUnsavedChanges = false;
            isChangingTemplate = false;

            // Reset Catalog Dropdown UI
            const catalogToggle = document.getElementById('catalogDropdownToggle');
            catalogToggle.textContent = 'Select Catalog...';
            document.getElementById('catalogName').value = '';
            document.getElementById('catalogName').readOnly = true;
            document.getElementById('catalogName').classList.remove('is-invalid', 'border-warning');

            // Reset Lot Dropdown UI
            const lotToggle = document.getElementById('lotDropdownToggle');
            lotToggle.textContent = 'Select Lot...';
            lotToggle.disabled = true;
            const lotItemsContainer = document.getElementById('lotDropdownItems');
            lotItemsContainer.innerHTML = '<div class="searchable-dropdown-no-results">Select a catalog first</div>';

            // Update button states, which will disable them
            updateButtonStates();
        }

        // Template radio button handler
        function handleTemplateRadioChange(event) {
            const newTemplateCode = event.target.value;
            /*
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
            */
            // Check for unsaved changes
            if (hasUnsavedChanges && currentTemplateCode !== newTemplateCode) {
                if (!confirm('You have unsaved changes. Do you want to switch templates and lose your changes?')) {
                    const oldRadio = document.getElementById(`template_${currentTemplateCode}`);
                    if (oldRadio) oldRadio.checked = true;
                    return;
                }
            }
            
            currentTemplateCode = newTemplateCode;
            
            // Reset catalog and lot selections
            resetCatalogAndLotSelection();
            
            // Load the structure for the new template (fields will be disabled)
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
                        
                        // All templates require lot tracking
                        const lotToggle = document.getElementById('lotDropdownToggle');
                        lotToggle.style.opacity = '1';
                        lotToggle.disabled = !currentCatalogNumber;
                        if (!currentLotNumber) {
                            lotToggle.textContent = 'Select Lot...';
                        }
                        
                        // Update button states after template structure is loaded
                        updateButtonStates();
                        
                        // If catalog is selected and not changing template, load data
                        if (currentCatalogNumber && !isChangingTemplate) {
                            loadCatalogData();
                        } else if (isChangingTemplate) {
                            displayEmptyTemplateData();
                            isChangingTemplate = false;
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
            const keyRowsMap = {
                'Source': 2,
                'Activity': 5,
                'Formulation': 3,
                'Stability & Storage': 4
            };

            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = '';
                
                if (templateKeys[sectionId] && templateKeys[sectionId].keys.length > 0) {
                    templateKeys[sectionId].keys.forEach(key => {
                        const keyName = key.key_name;
                        const keySource = key.key_source;
                        const sourceColor = keySource === 'catalog' ? 'red' : 'green';
                        const rows = keyRowsMap[keyName] || 2; // Default to 2 if not found
                        if(keyName == 'Source' || keyName == 'Activity' || keyName == 'Formulation' || keyName == 'Stability & Storage'){
                            const kvHtml = `
                                <div class="key-value-row" data-key="${keyName}" data-source="${keySource}">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <label class="form-label" style="color: ${sourceColor};">${keyName}</label>
                                        </div>
                                        <div class="col-md-8">
                                            <textarea class="form-control bulk-edit-textarea" 
                                                    id="textarea_${sectionId}_${keyName.replace(/\s+/g, '_')}" 
                                                    rows="${rows}" 
                                                    placeholder="Enter ${keyName.toLowerCase()}..."
                                                    disabled></textarea>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            container.insertAdjacentHTML('beforeend', kvHtml);
                        }
                        else{
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
                        }

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
            
            // All templates require lots - no special handling needed
            
            disableAllTextareas();
            
            const apiUrl = `api/get_section_data.php?catalog_number=${currentCatalogNumber}&template_code=${currentTemplateCode}&lot_number=${currentLotNumber || ''}`;
            
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        populateFieldsWithData(data.sections_data);
                        enableAllTextareas();
                        updateButtonStates();
                    } else {
                        console.error('Error loading data:', data.message);
                        enableAllTextareas();
                        updateButtonStates();
                    }
                })
                .catch(error => {
                    console.error('Error loading catalog data:', error);
                    enableAllTextareas();
                    updateButtonStates();
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
            
            // Clear any validation errors
            document.querySelectorAll('.is-invalid').forEach(element => {
                element.classList.remove('is-invalid');
            });
            document.querySelectorAll('.border-warning').forEach(element => {
                element.classList.remove('border-warning');
            });
            
            // Check if any fields are empty - if so, mark as having changes
            let hasEmptyFields = false;
            document.querySelectorAll('.bulk-edit-textarea:not(:disabled)').forEach(textarea => {
                if (!textarea.value.trim()) {
                    hasEmptyFields = true;
                    textarea.classList.add('border-warning'); // Visual indicator
                }
            });
            
            // If there are empty fields, enable save button
            if (hasEmptyFields) {
                hasUnsavedChanges = true;
                const saveBtn = document.getElementById('saveAllBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All*';
                }
            } else {
                hasUnsavedChanges = false;
                const saveBtn = document.getElementById('saveAllBtn');
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save All';
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
            const hasLot = currentLotNumber !== null && currentLotNumber !== undefined;
            
            // All templates require lots
            const canInteract = hasTemplate && hasCatalog && hasLot;
            
            // Save and Cancel buttons need interaction ability AND unsaved changes
            const canSaveOrCancel = canInteract && hasUnsavedChanges;
            document.getElementById('saveAllBtn').disabled = !canSaveOrCancel;
            document.getElementById('cancelBtn').disabled = !canSaveOrCancel;
            
            // Preview and Generate buttons only need interaction ability
            document.getElementById('previewBtn').disabled = !canInteract;
            document.getElementById('generateBtn').disabled = !canInteract;
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
            
            // All templates require lots
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
            
            // Prepare payload - lot_number can be empty string for lot-less templates
            const payload = {
                catalog_number: currentCatalogNumber,
                catalog_name: catalogName,
                lot_number: currentLotNumber || '', // Send empty string if no lot
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
                const selectedCatalog = catalogsData.find(cat => cat.catalog_number == currentCatalogNumber);
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
            
            // Reset lot selection when catalog changes
            currentLotNumber = null;
            const lotToggle = document.getElementById('lotDropdownToggle');
            
            // All templates require lots
            lotToggle.textContent = 'Select Lot...';
            
            // Clear lot dropdown items
            const lotItemsContainer = document.getElementById('lotDropdownItems');
            lotItemsContainer.innerHTML = '<div class="searchable-dropdown-no-results">Loading lots...</div>';
            
            // Update catalog name field
            const catalogNameField = document.getElementById('catalogName');
            catalogNameField.value = catalogName || '';
            catalogNameField.readOnly = false;
            
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
                            originalTemplateCode = data.template_code;
                            
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
                        
                        // Sort the 'templates' array by the 'template_name' property
                        data.templates.sort((a, b) => a.template_name.localeCompare(b.template_name));

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
                            radioLabel.textContent = template.template_name + ` (${template.template_code})`;
                            
                            // Active Enzymes template is default
                            if (radioInput.value === 'ACT') {
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
                    
                    // Enable lot dropdown (all templates require lots)
                    const lotToggle = document.getElementById('lotDropdownToggle');
                    if (lotToggle) {
                        lotToggle.disabled = false;
                        if (!currentLotNumber) {
                            lotToggle.textContent = 'Select Lot...';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading lots:', error);
                });
        }


        // PDF functions
        function previewPDF() {
            if (!currentCatalogNumber) {
                alert('Please select a catalog');
                return;
            }
            
            // All templates require lots
            if (!currentLotNumber) {
                alert('Please select a lot number');
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
            let previewUrl = `api/preview_pdf.php?catalog_number=${encodeURIComponent(currentCatalogNumber)}`;
            previewUrl += `&lot_number=${encodeURIComponent(currentLotNumber || '')}`; // Empty string if no lot
            
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

        function generatePDF() {
            if (!currentCatalogNumber) {
                alert('Please select a catalog');
                return;
            }
            if (!currentLotNumber) {
                alert('Please select a lot number');
                return;
            }
            if (hasUnsavedChanges) {
                alert('Please save all changes before generating the PDF');
                return;
            }
            if (!checkAllFieldsFilled()) {
                alert('Please fill all required fields before generating the PDF');
                return;
            }

            const generateBtn = document.getElementById('generateBtn');
            const originalText = generateBtn.innerHTML;
            generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generating...';
            generateBtn.disabled = true;

            // Build URL for generate API
            let generateUrl = `api/generate_pdf.php?catalog_number=${encodeURIComponent(currentCatalogNumber)}`;
            generateUrl += `&lot_number=${encodeURIComponent(currentLotNumber || '')}`;

            // Open the URL in a new tab. The PHP script will force a download.
            const pdfWindow = window.open(generateUrl, '_blank');

            // Check if popup was blocked
            if (!pdfWindow || pdfWindow.closed || typeof pdfWindow.closed == 'undefined') {
                alert('Please allow popups for PDF download.');
            }

            // Restore button after a short delay to allow the download to start
            setTimeout(() => {
                generateBtn.innerHTML = originalText;
                generateBtn.disabled = false;
                updateButtonStates();
            }, 1500);
            
            /*
            // AJAX call instead of opening a new tab
            fetch(generateUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showPDFGeneratedSuccess();
                    } else {
                        alert('PDF generation failed: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    alert('Error generating PDF: ' + error.message);
                })
                .finally(() => {
                    generateBtn.innerHTML = originalText;
                    generateBtn.disabled = false;
                    updateButtonStates();
                });
                */
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

        // Bulk Upload functionality
        let catalogUploadResults = null;
        let lotUploadResults = null;

        // Initialize bulk upload event listeners
        function initializeBulkUpload() {
            // Open modal button
            document.getElementById('bulkUploadBtn').addEventListener('click', function() {
                openBulkUploadModal();
            });
            
            // Download templates button
            document.getElementById('downloadTemplatesBtn').addEventListener('click', function() {
                downloadBothTemplates();
            });
            
            // Catalog file input
            document.getElementById('catalogExcelInput').addEventListener('change', function() {
                handleCatalogFileSelect(this);
            });
            
            // Lot file input
            document.getElementById('lotExcelInput').addEventListener('change', function() {
                handleLotFileSelect(this);
            });
            
            // Upload buttons
            document.getElementById('uploadCatalogBtn').addEventListener('click', function() {
                uploadCatalogFile();
            });
            
            document.getElementById('uploadLotBtn').addEventListener('click', function() {
                uploadLotFile();
            });
            
            // Download skipped report buttons
            // document.getElementById('downloadCatalogSkippedBtn').addEventListener('click', function() {
            //     downloadCatalogSkippedReport();
            // });
            
            // document.getElementById('downloadLotSkippedBtn').addEventListener('click', function() {
            //     downloadLotSkippedReport();
            // });
            
            // Reset on modal close
            document.getElementById('bulkUploadModal').addEventListener('hidden.bs.modal', function() {
                resetBulkUploadModal();
            });

            // Download updated report buttons
            document.getElementById('downloadCatalogUpdatedBtn')?.addEventListener('click', function() {
                downloadCatalogUpdatedReport();
            });
            
            document.getElementById('downloadLotUpdatedBtn')?.addEventListener('click', function() {
                downloadLotUpdatedReport();
            });

            document.getElementById('downloadCatalogCompleteBtn').addEventListener('click', function() {
                downloadCatalogCompleteReport();
            });

            document.getElementById('downloadLotCompleteBtn').addEventListener('click', function() {
                downloadLotCompleteReport();
            });

        }

        // Open bulk upload modal
        function openBulkUploadModal() {
            resetBulkUploadModal();
            const modal = new bootstrap.Modal(document.getElementById('bulkUploadModal'));
            modal.show();
        }

        // Download both templates
        function downloadBothTemplates() {
            // Download catalog template
            const catalogLink = document.createElement('a');
            catalogLink.href = 'upload_templates/catalogs_template.xlsx';
            catalogLink.download = 'catalogs_template.xlsx';
            document.body.appendChild(catalogLink);
            catalogLink.click();
            document.body.removeChild(catalogLink);
            
            // Small delay before downloading the second file
            setTimeout(() => {
                // Download lot template
                const lotLink = document.createElement('a');
                lotLink.href = 'upload_templates/lots_template.xlsx';
                lotLink.download = 'lots_template.xlsx';
                document.body.appendChild(lotLink);
                lotLink.click();
                document.body.removeChild(lotLink);
            }, 500);
        }

        // Handle catalog file selection
        function handleCatalogFileSelect(input) {
            const file = input.files[0];
            const uploadBtn = document.getElementById('uploadCatalogBtn');
            
            if (file) {
                // Validate file
                if (!validateExcelFile(file)) {
                    input.value = '';
                    uploadBtn.disabled = true;
                    return;
                }
                uploadBtn.disabled = false;
            } else {
                uploadBtn.disabled = true;
            }
        }

        // Handle lot file selection
        function handleLotFileSelect(input) {
            const file = input.files[0];
            const uploadBtn = document.getElementById('uploadLotBtn');
            
            if (file) {
                // Validate file
                if (!validateExcelFile(file)) {
                    input.value = '';
                    uploadBtn.disabled = true;
                    return;
                }
                uploadBtn.disabled = false;
            } else {
                uploadBtn.disabled = true;
            }
        }

        // Validate Excel file
        function validateExcelFile(file) {
            const fileName = file.name.toLowerCase();
            if (!fileName.endsWith('.xlsx') && !fileName.endsWith('.xls')) {
                alert('Please select an Excel file (.xlsx or .xls)');
                return false;
            }
            // Check file size (10MB max)
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                alert('File size exceeds 10MB limit');
                return false;
            }
            return true;
        }

        // Upload catalog file
        function uploadCatalogFile() {
            const fileInput = document.getElementById('catalogExcelInput');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file to upload');
                return;
            }
            
            // Show progress, hide results
            document.getElementById('catalogUploadProgress').style.display = 'block';
            document.getElementById('catalogResults').style.display = 'none';
            document.getElementById('uploadCatalogBtn').disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('file', file);
            formData.append('uploadType', 'catalog');
            
            // Make API call
            fetch('api/bulk_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                displayCatalogUploadResults(data);
            })
            .catch(error => {
                console.error('Catalog upload error:', error);
                displayCatalogUploadError('An unexpected error occurred during catalog upload.');
            })
            .finally(() => {
                document.getElementById('catalogUploadProgress').style.display = 'none';
                document.getElementById('uploadCatalogBtn').disabled = false;
            });
        }

        // Upload lot file
        function uploadLotFile() {
            const fileInput = document.getElementById('lotExcelInput');
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file to upload');
                return;
            }
            
            // Show progress, hide results
            document.getElementById('lotUploadProgress').style.display = 'block';
            document.getElementById('lotResults').style.display = 'none';
            document.getElementById('uploadLotBtn').disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('file', file);
            formData.append('uploadType', 'lot');
            
            // Make API call
            fetch('api/bulk_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                displayLotUploadResults(data);
            })
            .catch(error => {
                console.error('Lot upload error:', error);
                displayLotUploadError('An unexpected error occurred during lot upload.');
            })
            .finally(() => {
                document.getElementById('lotUploadProgress').style.display = 'none';
                document.getElementById('uploadLotBtn').disabled = false;
            });
        }

        // Display catalog upload results
        function displayCatalogUploadResults(data) {
            catalogUploadResults = data;
            
            const resultsSection = document.getElementById('catalogResults');
            resultsSection.style.display = 'block';
            
            if (data.success) {
                // Show success message
                const successAlert = document.getElementById('catalogSuccessAlert');
                const successMessage = document.getElementById('catalogSuccessMessage');
                
                if (data.status === 'success') {
                    successMessage.textContent = `All ${data.summary.totalRows} catalogs processed successfully.`;
                } else if (data.status === 'partial') {
                    successMessage.textContent = `Processing completed with some catalogs skipped.`;
                }
                
                successAlert.style.display = 'block';
                document.getElementById('catalogErrorAlert').style.display = 'none';
                
                // Show summary
                displayCatalogSummary(data.summary);
                
                // Reload catalogs
                loadCatalogs();
                
            } else {
                // Show error message
                displayCatalogUploadError(data.errorMessage || 'Catalog upload failed.');
            }
        }

        // Display lot upload results
        function displayLotUploadResults(data) {
            lotUploadResults = data;
            
            const resultsSection = document.getElementById('lotResults');
            resultsSection.style.display = 'block';
            
            if (data.success) {
                // Show success message
                const successAlert = document.getElementById('lotSuccessAlert');
                const successMessage = document.getElementById('lotSuccessMessage');
                
                if (data.status === 'success') {
                    successMessage.textContent = `All ${data.summary.totalRows} lots processed successfully.`;
                } else if (data.status === 'partial') {
                    successMessage.textContent = `Processing completed with some lots skipped.`;
                }
                
                successAlert.style.display = 'block';
                document.getElementById('lotErrorAlert').style.display = 'none';
                
                // Show summary
                displayLotSummary(data.summary);
                
                // Reload lots if a catalog is selected
                if (currentCatalogNumber) {
                    loadLots(currentCatalogNumber);
                }
                
            } else {
                // Show error message
                displayLotUploadError(data.errorMessage || 'Lot upload failed.');
            }
        }

        // Display catalog upload error
        function displayCatalogUploadError(message) {
            const resultsSection = document.getElementById('catalogResults');
            resultsSection.style.display = 'block';
            
            const errorAlert = document.getElementById('catalogErrorAlert');
            const errorMessage = document.getElementById('catalogErrorMessage');
            
            errorMessage.textContent = message;
            errorAlert.style.display = 'block';
            document.getElementById('catalogSuccessAlert').style.display = 'none';
            document.getElementById('catalogSummaryContainer').style.display = 'none';
        }

        // Display lot upload error
        function displayLotUploadError(message) {
            const resultsSection = document.getElementById('lotResults');
            resultsSection.style.display = 'block';
            
            const errorAlert = document.getElementById('lotErrorAlert');
            const errorMessage = document.getElementById('lotErrorMessage');
            
            errorMessage.textContent = message;
            errorAlert.style.display = 'block';
            document.getElementById('lotSuccessAlert').style.display = 'none';
            document.getElementById('lotSummaryContainer').style.display = 'none';
        }

        // Display catalog summary
        function displayCatalogSummary(summary) {
            const summaryContainer = document.getElementById('catalogSummaryContainer');
            const summaryText = document.getElementById('catalogSummaryText');
            
            let text = `<div style="color: #0066cc;">Total rows: ${summary.totalRows || 0}</div>`;
            text += `<div style="color: #28a745;">Successfully Added: ${summary.successCount || 0}</div>`;
            text += `<div style="color: #17a2b8;">Updated Records: ${summary.updateCount || 0}</div>`;
            if (summary.skippedCount > 0) {
                text += `<div style="color: #ff9900;">Skipped (Validation Failed): ${summary.skippedCount || 0}</div>`;
            }
            
            summaryText.innerHTML = text;
            summaryContainer.style.display = 'block';

            // Show complete report download button if available
            if (summary.completeReportPath) {
                document.getElementById('catalogCompleteSection').style.display = 'block';
            } else {
                document.getElementById('catalogCompleteSection').style.display = 'none';
            }

            // Show download button if there are updated records
            if (summary.updateCount > 0 && summary.updatedReportPath) {
                document.getElementById('catalogUpdatedSection').style.display = 'block';
            } else {
                document.getElementById('catalogUpdatedSection').style.display = 'none';
            }
        }

        // Display lot summary
        function displayLotSummary(summary) {
            const summaryContainer = document.getElementById('lotSummaryContainer');
            const summaryText = document.getElementById('lotSummaryText');
            
            let text = `<div style="color: #0066cc;">Total rows: ${summary.totalRows || 0}</div>`;
            text += `<div style="color: #28a745;">Successfully Added: ${summary.successCount || 0}</div>`;
            text += `<div style="color: #17a2b8;">Updated Records: ${summary.updateCount || 0}</div>`;
            if (summary.skippedCount > 0) {
                text += `<div style="color: #ff9900;">Skipped (Validation Failed): ${summary.skippedCount || 0}</div>`;
            }
            
            summaryText.innerHTML = text;
            summaryContainer.style.display = 'block';

            // Show complete report download button if available
            if (summary.completeReportPath) {
                document.getElementById('lotCompleteSection').style.display = 'block';
            } else {
                document.getElementById('lotCompleteSection').style.display = 'none';
            }
            
            // Show download button if there are updated records
            if (summary.updateCount > 0 && summary.updatedReportPath) {
                document.getElementById('lotUpdatedSection').style.display = 'block';
            } else {
                document.getElementById('lotUpdatedSection').style.display = 'none';
            }
        }


        // Download catalog complete report
        function downloadCatalogCompleteReport() {
            if (!catalogUploadResults || !catalogUploadResults.summary || !catalogUploadResults.summary.completeReportPath) {
                alert('No catalog upload report available');
                return;
            }
            
            const link = document.createElement('a');
            link.href = 'api/download_report.php?file=' + encodeURIComponent(catalogUploadResults.summary.completeReportPath);
            link.download = 'catalog_upload_report.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Download lot complete report
        function downloadLotCompleteReport() {
            if (!lotUploadResults || !lotUploadResults.summary || !lotUploadResults.summary.completeReportPath) {
                alert('No lot upload report available');
                return;
            }
            
            const link = document.createElement('a');
            link.href = 'api/download_report.php?file=' + encodeURIComponent(lotUploadResults.summary.completeReportPath);
            link.download = 'lot_upload_report.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Download catalog updated records report
        function downloadCatalogUpdatedReport() {
            if (!catalogUploadResults || !catalogUploadResults.summary || !catalogUploadResults.summary.updatedReportPath) {
                alert('No catalog updated records report available');
                return;
            }
            
            const link = document.createElement('a');
            link.href = 'api/download_report.php?file=' + encodeURIComponent(catalogUploadResults.summary.updatedReportPath);
            link.download = 'catalog_updated_records.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Download lot updated records report
        function downloadLotUpdatedReport() {
            if (!lotUploadResults || !lotUploadResults.summary || !lotUploadResults.summary.updatedReportPath) {
                alert('No lot updated records report available');
                return;
            }
            
            const link = document.createElement('a');
            link.href = 'api/download_report.php?file=' + encodeURIComponent(lotUploadResults.summary.updatedReportPath);
            link.download = 'lot_updated_records.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Reset bulk upload modal
        function resetBulkUploadModal() {
            // Clear file inputs
            document.getElementById('catalogExcelInput').value = '';
            document.getElementById('lotExcelInput').value = '';
            
            // Disable upload buttons
            document.getElementById('uploadCatalogBtn').disabled = true;
            document.getElementById('uploadLotBtn').disabled = true;
            
            // Hide all progress and results
            document.getElementById('catalogUploadProgress').style.display = 'none';
            document.getElementById('catalogResults').style.display = 'none';
            document.getElementById('catalogSuccessAlert').style.display = 'none';
            document.getElementById('catalogErrorAlert').style.display = 'none';
            document.getElementById('catalogSummaryContainer').style.display = 'none';
            document.getElementById('catalogUpdatedSection').style.display = 'none';
            
            document.getElementById('lotUploadProgress').style.display = 'none';
            document.getElementById('lotResults').style.display = 'none';
            document.getElementById('lotSuccessAlert').style.display = 'none';
            document.getElementById('lotErrorAlert').style.display = 'none';
            document.getElementById('lotSummaryContainer').style.display = 'none';
            document.getElementById('lotUpdatedSection').style.display = 'none';
            document.getElementById('catalogCompleteSection').style.display = 'none';
            document.getElementById('lotCompleteSection').style.display = 'none';
            
            // Clear results
            catalogUploadResults = null;
            lotUploadResults = null;
        }

    </script>
</body>
</html>