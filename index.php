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
        /* Updated styles for inline switching */
        .input-with-switch {
            position: relative;
            width: 100%;
        }
        .switch-to-search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            z-index: 10;
        }
        .inline-input {
            padding-right: 40px;
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
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 0.25rem;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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
                                <label for="catalogSelect" class="form-label">Catalog Number</label>
                                <div class="d-flex">
                                    <div id="catalogContainer" style="flex: 1;">
                                        <!-- This will contain either dropdown or input -->
                                        <div class="searchable-dropdown" id="catalogDropdown">
                                            <button type="button" class="searchable-dropdown-toggle" id="catalogDropdownToggle">
                                                Select Catalog...
                                            </button>
                                            <div class="searchable-dropdown-menu" id="catalogDropdownMenu">
                                                <div class="searchable-dropdown-search">
                                                    <input type="text" class="form-control form-control-sm" placeholder="Search catalog..." id="catalogSearchInput">
                                                </div>
                                                <div class="searchable-dropdown-items" id="catalogDropdownItems">
                                                    <div class="searchable-dropdown-no-results">Loading catalogs...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-primary ms-2" type="button" id="catalogModeToggleBtn" title="Add new catalog">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="catalogName" class="form-label">Catalog Name</label>
                                <input type="text" class="form-control" id="catalogName" placeholder="Enter catalog name">
                            </div>
                            <div class="col-md-4">
                                <label for="lotSelect" class="form-label">Lot Number</label>
                                <div class="d-flex">
                                    <div id="lotContainer" style="flex: 1;">
                                        <!-- This will contain either dropdown or input -->
                                        <div class="searchable-dropdown" id="lotDropdown">
                                            <button type="button" class="searchable-dropdown-toggle" id="lotDropdownToggle" disabled>
                                                Select Lot...
                                            </button>
                                            <div class="searchable-dropdown-menu" id="lotDropdownMenu">
                                                <div class="searchable-dropdown-search">
                                                    <input type="text" class="form-control form-control-sm" placeholder="Search lot..." id="lotSearchInput">
                                                </div>
                                                <div class="searchable-dropdown-items" id="lotDropdownItems">
                                                    <div class="searchable-dropdown-no-results">Select a catalog first</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-outline-primary ms-2" type="button" id="lotModeToggleBtn" disabled title="Add new lot">
                                        <i class="fas fa-plus"></i>
                                    </button>
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
<!-- Continue from Part 1 - Add this after the closing </div> of container -->

    <!-- Template Management Modal -->
    <div class="modal fade" id="templateManagementModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt me-2"></i>
                        Template Management
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Add New Template Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-plus me-2"></i>
                                Add New Template
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">  
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="newTemplateName" class="form-label">Template Name</label>
                                        <input type="text" class="form-control" id="newTemplateName" 
                                               placeholder="Enter template name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="newTemplateDescription" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="newTemplateDescription" 
                                               placeholder="Brief description">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="setAsDefault">
                                        <label class="form-check-label" for="setAsDefault">
                                            Set as default template
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="button" class="btn btn-success" id="createTemplateBtn">
                                        <i class="fas fa-plus me-1"></i>
                                        Create Template
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Templates Section -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Existing Templates
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="templatesListContainer">
                                <div class="text-center py-3">
                                    <i class="fas fa-spinner fa-spin me-2"></i>
                                    Loading templates...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Configuration Modal -->
    <div class="modal fade" id="templateConfigModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templateConfigModalTitle">
                        <i class="fas fa-cogs me-2"></i>
                        Configure Template Keys
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Add keys to each section. Specify whether each key comes from catalog data or lot-specific data.
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title" id="configTemplateInfo">Loading template...</h6>
                        </div>
                    </div>
                    
                    <div id="templateConfigSections">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading template configuration...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Template Key Modal -->
    <div class="modal fade" id="addTemplateKeyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Template Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="templateKeyName" class="form-label">Key Name</label>
                        <input type="text" class="form-control" id="templateKeyName" 
                               placeholder="Enter key name">
                    </div>
                    <div class="mb-3">
                        <label for="templateKeySource" class="form-label">Key Type</label>
                        <select class="form-select" id="templateKeySource">
                            <option value="catalog">Catalog Data</option>
                            <option value="lot">Lot Data</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveTemplateKeyBtn">
                        <i class="fas fa-plus me-1"></i>
                        Add Key
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Global variables
        let currentTemplateId = null;
        let currentCatalogId = null;
        let currentCatalogNumber = null;
        let currentLotNumber = null;
        let templateKeys = {};
        let currentData = {};
        let originalData = {};
        let hasUnsavedChanges = false;
        let isNewCatalog = false;
        let isNewLot = false;
        let catalogsData = [];
        let lotsData = [];
        let currentSectionIdForKey = null;

        // Initialize on DOM load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing new workflow...');
            initializeSearchableDropdowns();
            loadTemplates();
            loadCatalogs();
            initializeEventListeners();
            initializeTemplateManagementHandlers();
            initializeTemplateConfigHandlers();
        });

        // Template radio button handler
        function handleTemplateRadioChange(event) {
            const newTemplateId = event.target.value;
            
            // Check for unsaved changes
            if (hasUnsavedChanges && currentTemplateId !== newTemplateId) {
                if (!confirm('You have unsaved changes. Do you want to switch templates and lose your changes?')) {
                    // Revert radio selection
                    const oldRadio = document.getElementById(`template_${currentTemplateId}`);
                    if (oldRadio) oldRadio.checked = true;
                    return;
                }
            }
            
            currentTemplateId = newTemplateId;
            loadTemplateStructure(newTemplateId);
        }

        // Load template structure and display fields
        function loadTemplateStructure(templateId) {
            fetch(`api/get_template_keys.php?template_id=${templateId}`)
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
                        
                        // If catalog is selected, load data
                        if (currentCatalogId) {
                            loadCatalogData();
                        }
                    } else {
                        console.error('Failed to load template structure:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error loading template structure:', error);
                });
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

        // Toggle between dropdown and input for catalog
        function toggleCatalogMode() {
            const container = document.getElementById('catalogContainer');
            const toggleBtn = document.getElementById('catalogModeToggleBtn');
            const isDropdownMode = container.querySelector('.searchable-dropdown');
            
            if (isDropdownMode) {
                // Switch to input mode
                isNewCatalog = true;
                currentCatalogId = null;
                currentCatalogNumber = null;
                
                container.innerHTML = `
                    <div class="input-with-switch">
                        <input type="text" class="form-control inline-input" id="catalogNumberInput" 
                               placeholder="Enter new catalog number">
                        <button class="btn btn-sm btn-outline-secondary switch-to-search-btn" 
                                onclick="toggleCatalogMode()" title="Search existing">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                `;
                toggleBtn.innerHTML = '<i class="fas fa-search"></i>';
                toggleBtn.title = 'Search existing catalog';
                
                // Clear catalog name and reset lot
                document.getElementById('catalogName').value = '';
                resetLotSelection();
                disableAllTextareas();
                
                // Enable save/cancel buttons for new entry
                updateButtonStates();
                
            } else {
                // Switch back to dropdown mode
                isNewCatalog = false;
                
                container.innerHTML = `
                    <div class="searchable-dropdown" id="catalogDropdown">
                        <button type="button" class="searchable-dropdown-toggle" id="catalogDropdownToggle">
                            Select Catalog...
                        </button>
                        <div class="searchable-dropdown-menu" id="catalogDropdownMenu">
                            <div class="searchable-dropdown-search">
                                <input type="text" class="form-control form-control-sm" 
                                       placeholder="Search catalog..." id="catalogSearchInput">
                            </div>
                            <div class="searchable-dropdown-items" id="catalogDropdownItems">
                                <div class="searchable-dropdown-no-results">Loading catalogs...</div>
                            </div>
                        </div>
                    </div>
                `;
                
                toggleBtn.innerHTML = '<i class="fas fa-plus"></i>';
                toggleBtn.title = 'Add new catalog';
                
                // Reinitialize dropdown
                initializeCatalogDropdown();
                populateCatalogDropdown(catalogsData);
                
                // Reset states
                document.getElementById('catalogName').value = '';
                resetLotSelection();
                clearAllFields();
                updateButtonStates();
            }
        }

        // Toggle between dropdown and input for lot
        function toggleLotMode() {
            const container = document.getElementById('lotContainer');
            const toggleBtn = document.getElementById('lotModeToggleBtn');
            const isDropdownMode = container.querySelector('.searchable-dropdown');
            
            if (isDropdownMode) {
                // Switch to input mode
                isNewLot = true;
                currentLotNumber = null;
                
                container.innerHTML = `
                    <div class="input-with-switch">
                        <input type="text" class="form-control inline-input" id="lotNumberInput" 
                               placeholder="Enter new lot number">
                        <button class="btn btn-sm btn-outline-secondary switch-to-search-btn" 
                                onclick="toggleLotMode()" title="Search existing">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                `;
                toggleBtn.innerHTML = '<i class="fas fa-search"></i>';
                toggleBtn.title = 'Search existing lot';
                
                // Clear lot-specific fields
                clearLotFields();
                
                // Enable textareas if catalog is selected
                if (currentCatalogId || isNewCatalog) {
                    enableAllTextareas();
                }
                updateButtonStates();
                
            } else {
                // Switch back to dropdown mode
                isNewLot = false;
                
                container.innerHTML = `
                    <div class="searchable-dropdown" id="lotDropdown">
                        <button type="button" class="searchable-dropdown-toggle" id="lotDropdownToggle">
                            Select Lot...
                        </button>
                        <div class="searchable-dropdown-menu" id="lotDropdownMenu">
                            <div class="searchable-dropdown-search">
                                <input type="text" class="form-control form-control-sm" 
                                       placeholder="Search lot..." id="lotSearchInput">
                            </div>
                            <div class="searchable-dropdown-items" id="lotDropdownItems">
                                <div class="searchable-dropdown-no-results">Select a catalog first</div>
                            </div>
                        </div>
                    </div>
                `;
                
                toggleBtn.innerHTML = '<i class="fas fa-plus"></i>';
                toggleBtn.title = 'Add new lot';
                
                // Reinitialize dropdown
                initializeLotDropdown();
                if (currentCatalogId) {
                    populateLotDropdown(lotsData);
                }
                
                // Clear lot fields and disable textareas
                clearLotFields();
                if (!currentCatalogId) {
                    disableAllTextareas();
                }
                updateButtonStates();
            }
        }

        // Load catalog data when catalog is selected
        function loadCatalogData() {
            if (!currentTemplateId || !currentCatalogId) return;
            
            disableAllTextareas();
            
            fetch(`api/get_section_data.php?catalog_id=${currentCatalogId}&template_id=${currentTemplateId}&lot_number=${currentLotNumber || ''}`)
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

        // Clear lot-specific fields
        function clearLotFields() {
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                if (templateKeys[sectionId]) {
                    templateKeys[sectionId].keys.forEach(key => {
                        if (key.key_source === 'lot') {
                            const textarea = document.getElementById(`textarea_${sectionId}_${key.key_name.replace(/\s+/g, '_')}`);
                            if (textarea) {
                                textarea.value = '';
                            }
                        }
                    });
                }
            });
        }

        // Clear all fields
        function clearAllFields() {
            document.querySelectorAll('.bulk-edit-textarea').forEach(textarea => {
                textarea.value = '';
                textarea.classList.remove('is-invalid', 'border-warning');
            });
            hasUnsavedChanges = false;
        }

        // Update button states
        function updateButtonStates() {
            const hasTemplate = !!currentTemplateId;
            const hasCatalog = !!(currentCatalogId || isNewCatalog);
            const hasLot = !!(currentLotNumber || isNewLot);
            const canSave = hasTemplate && hasCatalog && hasLot;
            
            document.getElementById('saveAllBtn').disabled = !canSave;
            document.getElementById('cancelBtn').disabled = !canSave;
            document.getElementById('previewBtn').disabled = !canSave;
            document.getElementById('generateBtn').disabled = !canSave;
            
            // Lot button state
            document.getElementById('lotModeToggleBtn').disabled = !hasCatalog;
            const lotToggle = document.getElementById('lotDropdownToggle');
            if (lotToggle) {
                lotToggle.disabled = !hasCatalog;
            }
        }

        // Track changes
        function markFieldAsChanged(textarea) {
            textarea.classList.add('border-warning');
            hasUnsavedChanges = true;
        }

        // Validate all fields
        function validateAllFields() {
            let isValid = true;
            
            document.querySelectorAll('.bulk-edit-textarea:not(:disabled)').forEach(textarea => {
                if (!textarea.value.trim()) {
                    textarea.classList.add('is-invalid');
                    const feedback = textarea.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = 'This field is required';
                    }
                    isValid = false;
                } else {
                    textarea.classList.remove('is-invalid');
                }
            });
            
            // Validate catalog name
            const catalogName = document.getElementById('catalogName');
            if (!catalogName.value.trim()) {
                catalogName.classList.add('is-invalid');
                isValid = false;
            } else {
                catalogName.classList.remove('is-invalid');
            }
            
            // Validate new inputs if in input mode
            if (isNewCatalog) {
                const catalogInput = document.getElementById('catalogNumberInput');
                if (catalogInput && !catalogInput.value.trim()) {
                    catalogInput.classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            if (isNewLot) {
                const lotInput = document.getElementById('lotNumberInput');
                if (lotInput && !lotInput.value.trim()) {
                    lotInput.classList.add('is-invalid');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        // Save all data
        function saveAllData() {
            if (!validateAllFields()) {
                alert('Please fill in all required fields before saving.');
                return;
            }
            
            // TODO: Implement bulk save API call
            console.log('Save all data - To be implemented');
            alert('Save functionality will be implemented in the next phase');
        }

        // Cancel changes
        function cancelChanges() {
            if (hasUnsavedChanges) {
                if (!confirm('Are you sure you want to cancel all changes?')) {
                    return;
                }
            }
            
            // Restore original values
            Object.keys(originalData).forEach(sectionId => {
                Object.keys(originalData[sectionId]).forEach(key => {
                    const textarea = document.getElementById(`textarea_${sectionId}_${key.replace(/\s+/g, '_')}`);
                    if (textarea) {
                        textarea.value = originalData[sectionId][key];
                        textarea.classList.remove('is-invalid', 'border-warning');
                    }
                });
            });
            
            hasUnsavedChanges = false;
        }

        // Event listeners
        function initializeEventListeners() {
            // Template management button
            const manageBtn = document.createElement('button');
            manageBtn.className = 'btn btn-sm btn-outline-secondary ms-3';
            manageBtn.innerHTML = '<i class="fas fa-cog"></i> Manage Templates';
            manageBtn.onclick = openTemplateManagement;
            document.getElementById('templateRadioButtons').parentElement.appendChild(manageBtn);
            
            // Mode toggle buttons
            document.getElementById('catalogModeToggleBtn').addEventListener('click', toggleCatalogMode);
            document.getElementById('lotModeToggleBtn').addEventListener('click', toggleLotMode);
            
            // Action buttons
            document.getElementById('saveAllBtn').addEventListener('click', saveAllData);
            document.getElementById('cancelBtn').addEventListener('click', cancelChanges);
            document.getElementById('previewBtn').addEventListener('click', previewPDF);
            document.getElementById('generateBtn').addEventListener('click', generatePDF);
            
            // Catalog name change tracking
            document.getElementById('catalogName').addEventListener('input', function() {
                markFieldAsChanged(this);
            });
            
            // Track changes in textareas (using event delegation)
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('bulk-edit-textarea')) {
                    markFieldAsChanged(e.target);
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
                    filterDropdownItems('catalog', this.value);
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
                    filterDropdownItems('lot', this.value);
                });
                search.addEventListener('click', e => e.stopPropagation());
            }
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
                    filterDropdownItems(type, '');
                }, 100);
            }
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.searchable-dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        function filterDropdownItems(type, searchTerm) {
            const container = document.getElementById(`${type}DropdownItems`);
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
            
            // Show no results message if needed
            if (!hasVisibleItems && items.length > 0) {
                let noResults = container.querySelector('.no-search-results');
                if (!noResults) {
                    noResults = document.createElement('div');
                    noResults.className = 'searchable-dropdown-no-results no-search-results';
                    noResults.textContent = 'No matching results found';
                    container.appendChild(noResults);
                }
            } else {
                const noResults = container.querySelector('.no-search-results');
                if (noResults) noResults.remove();
            }
        }

        function selectCatalogFromDropdown(catalogId, catalogNumber, catalogName) {
            const toggle = document.getElementById('catalogDropdownToggle');
            toggle.textContent = catalogNumber;
            
            currentCatalogId = catalogId;
            currentCatalogNumber = catalogNumber;
            isNewCatalog = false;
            
            // Update catalog name field
            document.getElementById('catalogName').value = catalogName || '';
            
            closeAllDropdowns();
            
            // Load lots for this catalog
            loadLots(catalogId);
            
            // Load catalog data if template is selected
            if (currentTemplateId) {
                loadCatalogData();
            }
            
            updateButtonStates();
        }

        function selectLotFromDropdown(lotNumber) {
            const toggle = document.getElementById('lotDropdownToggle');
            toggle.textContent = lotNumber;
            
            currentLotNumber = lotNumber;
            isNewLot = false;
            
            closeAllDropdowns();
            
            // Load lot data if template and catalog are selected
            if (currentTemplateId && currentCatalogId) {
                loadCatalogData();
            }
            
            updateButtonStates();
        }

        function populateCatalogDropdown(catalogs) {
            const container = document.getElementById('catalogDropdownItems');
            container.innerHTML = '';
            
            if (catalogs.length === 0) {
                container.innerHTML = '<div class="searchable-dropdown-no-results">No catalogs found</div>';
            } else {
                catalogs.forEach(catalog => {
                    const item = document.createElement('div');
                    item.className = 'searchable-dropdown-item';
                    item.textContent = catalog.catalog_number;
                    item.onclick = () => selectCatalogFromDropdown(
                        catalog.id, 
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
                container.innerHTML = '<div class="searchable-dropdown-no-results">No lots found</div>';
            } else {
                lots.forEach(lot => {
                    const item = document.createElement('div');
                    item.className = 'searchable-dropdown-item';
                    item.textContent = lot.lot_number;
                    item.onclick = () => selectLotFromDropdown(lot.lot_number);
                    container.appendChild(item);
                });
            }
        }

        function resetLotSelection() {
            currentLotNumber = null;
            isNewLot = false;
            
            const container = document.getElementById('lotContainer');
            if (container.querySelector('.searchable-dropdown')) {
                const toggle = document.getElementById('lotDropdownToggle');
                if (toggle) {
                    toggle.textContent = 'Select Lot...';
                    toggle.disabled = true;
                }
                clearDropdown('lot');
            }
            
            document.getElementById('lotModeToggleBtn').disabled = true;
            lotsData = [];
        }

        function clearDropdown(type) {
            const toggle = document.getElementById(`${type}DropdownToggle`);
            if (toggle) {
                toggle.textContent = type === 'catalog' ? 'Select Catalog...' : 'Select Lot...';
            }
            
            const container = document.getElementById(`${type}DropdownItems`);
            if (container) {
                container.innerHTML = `<div class="searchable-dropdown-no-results">No items available</div>`;
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
                        
                        data.templates.forEach(template => {
                            const radioDiv = document.createElement('div');
                            radioDiv.className = 'template-radio';
                            
                            const radioInput = document.createElement('input');
                            radioInput.type = 'radio';
                            radioInput.name = 'templateRadio';
                            radioInput.id = `template_${template.id}`;
                            radioInput.value = template.id;
                            radioInput.className = 'form-check-input';
                            radioInput.addEventListener('change', handleTemplateRadioChange);
                            
                            const radioLabel = document.createElement('label');
                            radioLabel.htmlFor = `template_${template.id}`;
                            radioLabel.textContent = template.template_name;
                            if (template.is_default) {
                                radioLabel.textContent += ' (Default)';
                                radioInput.checked = true;
                                currentTemplateId = template.id;
                                loadTemplateStructure(template.id);
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

        function loadLots(catalogId) {
            fetch(`api/get_lot_data.php?catalog_id=${catalogId}`)
                .then(response => response.json())
                .then(data => {
                    lotsData = Array.isArray(data) ? data : [];
                    populateLotDropdown(lotsData);
                    
                    // Enable lot dropdown
                    const lotToggle = document.getElementById('lotDropdownToggle');
                    if (lotToggle) {
                        lotToggle.disabled = false;
                    }
                    document.getElementById('lotModeToggleBtn').disabled = false;
                })
                .catch(error => {
                    console.error('Error loading lots:', error);
                });
        }

        // Template management functions
        function openTemplateManagement() {
            loadTemplatesList();
            new bootstrap.Modal(document.getElementById('templateManagementModal')).show();
        }

        function loadTemplatesList() {
            const container = document.getElementById('templatesListContainer');
            container.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
            
            fetch('api/get_templates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.templates.length > 0) {
                        let html = '';
                        data.templates.forEach(template => {
                            const defaultBadge = template.is_default ? '<span class="badge bg-primary ms-2">Default</span>' : '';
                            html += `
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-1">${template.template_name}${defaultBadge}</h6>
                                                <small class="text-muted">${template.description || 'No description'}</small>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        onclick="configureTemplateKeys(${template.id}, '${template.template_name}')">
                                                    <i class="fas fa-cogs"></i> Configure
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteTemplate(${template.id}, '${template.template_name}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<div class="text-center text-muted">No templates found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading templates list:', error);
                    container.innerHTML = '<div class="alert alert-danger">Error loading templates</div>';
                });
        }

        function initializeTemplateManagementHandlers() {
            document.getElementById('createTemplateBtn').addEventListener('click', createNewTemplate);
        }

        function initializeTemplateConfigHandlers() {
            document.getElementById('saveTemplateKeyBtn').addEventListener('click', saveTemplateKey);
        }

        function createNewTemplate() {
            const name = document.getElementById('newTemplateName').value.trim();
            const description = document.getElementById('newTemplateDescription').value.trim();
            const isDefault = document.getElementById('setAsDefault').checked;
            
            if (!name) {
                alert('Please enter a template name');
                return;
            }
            
            fetch('api/create_template.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    template_name: name,
                    description: description,
                    is_default: isDefault
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newTemplateName').value = '';
                    document.getElementById('newTemplateDescription').value = '';
                    document.getElementById('setAsDefault').checked = false;
                    loadTemplatesList();
                    loadTemplates();
                    alert('Template created successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error creating template:', error);
                alert('Error creating template');
            });
        }

        function deleteTemplate(templateId, templateName) {
            if (!confirm(`Delete template "${templateName}"?`)) return;
            
            fetch('api/delete_template.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ template_id: templateId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTemplatesList();
                    loadTemplates();
                    alert('Template deleted successfully!');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting template:', error);
                alert('Error deleting template');
            });
        }

        function configureTemplateKeys(templateId, templateName) {
            currentTemplateId = templateId;
            document.getElementById('templateConfigModalTitle').innerHTML = 
                `<i class="fas fa-cogs me-2"></i> Configure: ${templateName}`;
            loadTemplateConfiguration(templateId);
            new bootstrap.Modal(document.getElementById('templateConfigModal')).show();
        }

        function loadTemplateConfiguration(templateId) {
            const container = document.getElementById('templateConfigSections');
            container.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
            
            fetch(`api/get_template_keys.php?template_id=${templateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('configTemplateInfo').textContent = 
                            data.template_info.template_name + 
                            (data.template_info.description ? ` - ${data.template_info.description}` : '');
                        
                        let html = '';
                        data.sections.forEach(section => {
                            html += `
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">${section.section_name}</h6>
                                            <button class="btn btn-sm btn-primary" 
                                                    onclick="addKeyToSection(${section.section_id}, '${section.section_name}')">
                                                <i class="fas fa-plus me-1"></i> Add Key
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        ${section.keys.length === 0 ? 
                                            '<div class="text-muted text-center py-3">No keys defined</div>' : 
                                            section.keys.map(key => createKeyRowHtml(key)).join('')}
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    }
                })
                .catch(error => {
                    console.error('Error loading template configuration:', error);
                    container.innerHTML = '<div class="alert alert-danger">Error loading configuration</div>';
                });
        }

        function createKeyRowHtml(key) {
            const sourceColor = key.key_source === 'catalog' ? 'bg-danger' : 'bg-success';
            return `
                <div class="border rounded p-3 mb-2">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <strong>${key.key_name}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="badge ${sourceColor}">${key.key_source}</span>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteTemplateKey(${key.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        function addKeyToSection(sectionId, sectionName) {
            currentSectionIdForKey = sectionId;
            document.querySelector('#addTemplateKeyModal .modal-title').textContent = `Add Key to ${sectionName}`;
            document.getElementById('templateKeyName').value = '';
            document.getElementById('templateKeySource').value = 'catalog';
            new bootstrap.Modal(document.getElementById('addTemplateKeyModal')).show();
        }

        function saveTemplateKey() {
            const keyName = document.getElementById('templateKeyName').value.trim();
            const keySource = document.getElementById('templateKeySource').value;
            
            if (!keyName) {
                alert('Please enter a key name');
                return;
            }
            
            fetch('api/add_template_key.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    template_id: currentTemplateId,
                    section_id: currentSectionIdForKey,
                    key_name: keyName,
                    key_source: keySource
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addTemplateKeyModal')).hide();
                    loadTemplateConfiguration(currentTemplateId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error adding template key:', error);
                alert('Error adding key');
            });
        }

        function deleteTemplateKey(keyId) {
            if (!confirm('Delete this key?')) return;
            
            fetch('api/delete_template_key.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ key_id: keyId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTemplateConfiguration(currentTemplateId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting template key:', error);
                alert('Error deleting key');
            });
        }

        // PDF functions
        function previewPDF() {
            // TODO: Implement preview
            alert('Preview PDF functionality to be implemented');
        }

        function generatePDF() {
            // TODO: Implement generate
            alert('Generate PDF functionality to be implemented');
        }
    </script>
</body>
</html>