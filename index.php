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
            /* border: 1px solid #dee2e6; */
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .section-header {
            /* background-color: #f8f9fa; */
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        .section-content {
            padding: 8px;
            padding-top:0px;
            padding-bottom: 0px;
            overflow: auto;
        }
        .card-body{
            padding: 0.9rem;
        }
        .key-value-row {
            background-color: #ffffff;
            /* border: 1px solid #e9ecef; */
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 0px;
            cursor: move;
            transition: all 0.2s ease;
        }
        .key-value-row:hover {
            background-color: #f8f9fa;
            border-color: #6c757d;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .key-value-row.sortable-ghost {
            opacity: 0.5;
            background-color: #e3f2fd;
            border-color: #2196f3;
        }
        .key-value-row.sortable-chosen {
            background-color: #f0f8ff;
            border-color: #007bff;
        }
        .drag-handle {
            cursor: grab;
            color: #6c757d;
            transition: color 0.2s ease;
        }
        .drag-handle:hover {
            color: #007bff;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .collapse-icon {
            transition: transform 0.3s ease;
        }
        .collapsed .collapse-icon {
            transform: rotate(-90deg);
        }
        .btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }
        .btn-outline-danger:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .source-badge {
            font-size: 0.75rem;
        }
        .header-logo {
            height: 40px;
        }
        .template-section {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        /* New template-based workflow styles */
        .value-display {
            min-height: 40px;
            padding: 8px 0;
        }
        .value-text {
            display: block;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            min-height: 38px;
            word-wrap: break-word;
        }
        .value-edit textarea {
            resize: vertical;
            min-height: 60px;
        }
        .edit-btn, .save-btn, .cancel-btn {
            margin-left: 5px;
            min-width: 70px;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .key-value-row .col-md-4,
            .key-value-row .col-md-6,
            .key-value-row .col-md-2 {
                margin-bottom: 10px;
            }
            .edit-btn, .save-btn, .cancel-btn {
                width: 100%;
                margin-left: 0;
                margin-bottom: 5px;
            }
        }
        
        /* Success feedback */
        .btn-outline-success {
            color: #28a745;
            border-color: #28a745;
        }
        .btn-outline-success:hover {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
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
                    <!-- <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Configuration
                        </h5>
                    </div> -->
                    <div class="card-body">
                        <!-- Template Selection -->
                        <!-- <div class="template-section">
                            <div class="row">
                                <div class="col-md-8">
                                    <label for="templateSelect" class="form-label">
                                        <i class="fas fa-file-alt me-1"></i>
                                        Template
                                    </label>
                                    <select class="form-select" id="templateSelect">
                                        <option value="">Loading templates...</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button class="btn btn-outline-secondary" type="button" id="manageTemplateBtn">
                                        <i class="fas fa-cog me-1"></i>
                                        Manage Templates
                                    </button>
                                </div>
                            </div>
                        </div> -->

                        <!-- Catalog and Lot Selection -->
                        <div class="row">
                            <div class="col-md-3">
                                <label for="templateSelect" class="form-label">
                                    <i class="fas fa-file-alt me-1"></i>
                                    Template
                                </label>
                                <div class="input-group">
                                    <select class="form-select" id="templateSelect">
                                        <option value="">Loading templates...</option>
                                    </select>                                    
                                    <button class="btn btn-outline-primary" type="button" id="manageTemplateBtn">
                                        <!-- <i class="fas fa-cog me-1"></i> -->
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="catalogSelect" class="form-label">Catalog Number</label>
                                <div class="input-group">
                                    <select class="form-select" id="catalogSelect">
                                        <option value="">Select Catalog...</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" id="addCatalogBtn">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="catalogName" class="form-label">Catalog Name</label>
                                <input type="text" class="form-control" id="catalogName" placeholder="Enter catalog name" readonly>
                            </div>
                            <div class="col-md-3">
                                <label for="lotSelect" class="form-label">Lot Number</label>
                                <div class="input-group">
                                    <select class="form-select" id="lotSelect" disabled>
                                        <option value="">Select Lot...</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" id="addLotBtn" disabled>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-success me-2" id="loadDataBtn" disabled>
                                    <i class="fas fa-download me-1"></i>
                                    Load Data
                                </button>
                                <button class="btn btn-outline-primary me-2" id="createNewBtn" disabled>
                                    <i class="fas fa-magic me-1"></i>
                                    Create New CoA
                                </button>
                                <button class="btn btn-outline-secondary" id="clearDataBtn">
                                    <i class="fas fa-eraser me-1"></i>
                                    Clear Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Certificate Header -->
        <div class="row mb-4" id="certificateHeader" hidden>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="mb-3" id="catalogTitle">Select a catalog to begin</h2>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Catalog Number:</strong> <span id="displayCatalogNumber">Not selected</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Lot Number:</strong> <span id="displayLotNumber">Not selected</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Template:</strong> <span id="displayTemplate">Not selected</span>
                            </div>
                        </div>
                        <h4 class="mt-3 text-primary">Certificate of Analysis</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Sections Container -->
        <div id="sectionsContainer">
            <!-- Description Section -->
            <div class="section-card" data-section-id="1">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">Description</h5>
                        </div>
                    </div>
                </div>
                <div class="section-content" id="section_1">
                    <div class="key-values-container" id="keyValues_1">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select template, catalog and lot, then click "Load Data" to view template
                        </div>
                    </div>
                </div>
            </div>

            <!-- Specifications Section -->
            <div class="section-card" data-section-id="2">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">Specifications</h5>
                        </div>
                    </div>
                </div>
                <div class="section-content" id="section_2">
                    <div class="key-values-container" id="keyValues_2">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select template, catalog and lot, then click "Load Data" to view template
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preparation and Storage Section -->
            <div class="section-card" data-section-id="3">
                <div class="section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">Preparation and Storage</h5>
                        </div>
                    </div>
                </div>
                <div class="section-content" id="section_3">
                    <div class="key-values-container" id="keyValues_3">
                        <div class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Select template, catalog and lot, then click "Load Data" to view template
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-2 mb-3">
            <div class="col-12 text-center">
                <button class="btn btn-primary me-3" id="previewBtn" disabled>
                    <i class="fas fa-eye me-2"></i>
                    Preview PDF
                </button>
                <button class="btn btn-success" id="generateBtn" disabled>
                    <i class="fas fa-file-pdf me-2"></i>
                    Generate PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Add Catalog Modal -->
    <div class="modal fade" id="addCatalogModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Catalog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="newCatalogName" class="form-label">Catalog Name</label>
                        <input type="text" class="form-control" id="newCatalogName" placeholder="Enter catalog name (e.g., AKT (SGK) Substrate)">
                        <div class="form-text">This will be the product name displayed at the top of the CoA.</div>
                    </div>
                    <div class="mb-3">
                        <label for="newCatalogNumber" class="form-label">Catalog Number</label>
                        <input type="text" class="form-control" id="newCatalogNumber" placeholder="Enter catalog number (e.g., A08-58)">
                        <div class="form-text">This will be the unique identifier for your catalog.</div>
                    </div>
                    <div class="mb-3">
                        <label for="catalogTemplateSelect" class="form-label">Template (Optional)</label>
                        <select class="form-select" id="catalogTemplateSelect">
                            <option value="">Use default template</option>
                        </select>
                        <div class="form-text">Select a specific template or leave blank to use the default.</div>
                    </div>
                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle me-1"></i>Both catalog name and number will be created if they don't exist in the database.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCatalogBtn">Add Catalog</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Lot Modal -->
    <div class="modal fade" id="addLotModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Lot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="selectedCatalogInfo" class="form-label">Catalog</label>
                        <input type="text" class="form-control" id="selectedCatalogInfo" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="newLotNumber" class="form-label">Lot Number</label>
                        <input type="text" class="form-control" id="newLotNumber" placeholder="Enter lot number (e.g., Z1157-5)">
                        <div class="form-text">This will be the unique identifier for this lot within the catalog.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveLotBtn">Add Lot</button>
                </div>
            </div>
        </div>
    </div>

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
                                <!-- Template Name and Description -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="newTemplateName" class="form-label">Template Name</label>
                                        <input type="text" class="form-control" id="newTemplateName" 
                                               placeholder="Enter template name (e.g., Protein Analysis Template)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="newTemplateDescription" class="form-label">Description</label>
                                        <input type="text" class="form-control" id="newTemplateDescription" 
                                               placeholder="Brief description of template purpose">
                                    </div>
                                </div>
                            </div>

                            <!-- Key Input Section -->
                            <h6>Initial Keys</h6>
                            <p class="text-muted">Add initial keys for each section. You can configure more keys later.</p>

                            <!-- Description Section Keys -->
                            <div class="mb-3">
                                <label class="form-label">Description Section Keys</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control description-key-name" placeholder="Key Name (e.g., Product Code)">
                                    <select class="form-select description-key-type">
                                        <option value="catalog">Catalog</option>
                                        <option value="lot">Lot</option>
                                    </select>
                                    <button class="btn btn-outline-secondary add-key-btn" type="button" data-section="description">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="descriptionKeysContainer"></div>
                            </div>

                            <!-- Specifications Section Keys -->
                            <div class="mb-3">
                                <label class="form-label">Specifications Section Keys</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control specifications-key-name" placeholder="Key Name (e.g., Purity)">
                                    <select class="form-select specifications-key-type">
                                        <option value="catalog">Catalog</option>
                                        <option value="lot">Lot</option>
                                    </select>
                                    <button class="btn btn-outline-secondary add-key-btn" type="button" data-section="specifications">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                  <div id="specificationsKeysContainer"></div>
                            </div>

                            <!-- Preparation and Storage Section Keys -->
                            <div class="mb-3">
                                <label class="form-label">Preparation and Storage Section Keys</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control preparation-key-name" placeholder="Key Name (e.g., Storage Temp)">
                                    <select class="form-select preparation-key-type">
                                        <option value="catalog">Catalog</option>
                                        <option value="lot">Lot</option>
                                    </select>
                                    <button class="btn btn-outline-secondary add-key-btn" type="button" data-section="preparation">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div id="preparationKeysContainer"></div>
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
                    
                    <!-- Template Info -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title" id="configTemplateInfo">Loading template...</h6>
                        </div>
                    </div>
                    
                    <!-- Sections Configuration -->
                    <div id="templateConfigSections">
                        <!-- Sections will be loaded here -->
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p class="mt-2">Loading template configuration...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" id="saveTemplateConfigBtn">
                        <i class="fas fa-save me-1"></i>
                        Save Configuration
                    </button>
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
                               placeholder="Enter key name (e.g., Purity, Source, Expiry Date)">
                    </div>
                    <div class="mb-3">
                        <label for="templateKeySource" class="form-label">Key Type</label>
                        <select class="form-select" id="templateKeySource">
                            <option value="catalog">Catalog Data (same for all lots)</option>
                            <option value="lot">Lot Data (specific to each lot)</option>
                        </select>
                        <div class="form-text">
                            <strong>Catalog:</strong> Data that's the same for all lots (e.g., molecular weight, formulation)<br>
                            <strong>Lot:</strong> Data that varies by lot (e.g., expiry date, batch purity)
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    
    <script>
        // GLOBAL VARIABLES - DECLARED ONLY ONCE
        let currentSectionId = null;
        let sectionsData = {};
        let sectionCounter = 0;
        let currentTemplateId = null;
        let currentSectionIdForKey = null;
        let currentCatalogId = null;
        let currentLotNumber = null;
        let templateKeys = {}; // Store template structure
        let catalogData = {}; // Store catalog values
        let lotData = {}; // Store lot values

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing template-based workflow...');
            
            loadCatalogs();
            loadTemplates();
            initializeEventListeners();
            initializeTemplateHandlers();
            initializeTemplateManagementHandlers();
            initializeTemplateConfigHandlers();
            
            // Initialize sections data and disable buttons on page load
            sectionsData = {};
            
            const previewBtn = document.getElementById('previewBtn');
            const generateBtn = document.getElementById('generateBtn');
            
            if (previewBtn) previewBtn.disabled = true;
            if (generateBtn) generateBtn.disabled = true;
            
            console.log('Application initialized successfully');
        });

        // Load data based on selections
        function loadData() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            const templateId = document.getElementById('templateSelect').value;
            
            if (!templateId) {
                alert('Please select a template first');
                return;
            }
            
            if (!catalogId) {
                alert('Please select a catalog');
                return;
            }
            
            if (!catalogName) {
                alert('Please enter a catalog name');
                return;
            }
            
            if (!lotNumber) {
                alert('Please select a lot number');
                return;
            }

            currentCatalogId = catalogId;
            currentLotNumber = lotNumber;
            currentTemplateId = templateId;
            
            const selectedOption = document.getElementById('catalogSelect').options[document.getElementById('catalogSelect').selectedIndex];
            const catalogNumber = selectedOption.getAttribute('data-catalog-number');
            
            // Update header display
            document.getElementById('catalogTitle').textContent = catalogName;
            document.getElementById('displayCatalogNumber').textContent = catalogNumber;
            document.getElementById('displayLotNumber').textContent = lotNumber;
            updateTemplateDisplay();

            showLoadingInSections();
            loadTemplateBasedData(catalogId, lotNumber);
        }

        // Load template-based data
        function loadTemplateBasedData(catalogId, lotNumber) {
            // Get the currently selected template_id from the dropdown
            const templateSelect = document.getElementById('templateSelect');
            const selectedTemplateId = templateSelect.value;
            
            if (!selectedTemplateId) {
                throw new Error('Please select a template first');
            }
            
            // Store template_id globally
            currentTemplateId = selectedTemplateId;
            
            // Load template structure first
            loadTemplateStructure(selectedTemplateId)
                .then(() => {
                    // Load existing data with template_id parameter
                    return loadExistingData(catalogId, lotNumber, selectedTemplateId);
                })
                .then(() => {
                    displayTemplateBasedSections();
                    document.getElementById('previewBtn').disabled = false;
                    document.getElementById('generateBtn').disabled = false;
                })
                .catch(error => {
                    console.error('Error loading template-based data:', error);
                    showErrorInSections(error.message);
                });
        }

        // Load template structure
        function loadTemplateStructure(templateId) {
            return fetch(`api/get_template_keys.php?template_id=${templateId}`)
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
                        console.log('Template structure loaded:', templateKeys);
                    } else {
                        throw new Error('Failed to load template structure: ' + data.message);
                    }
                });
        }

        // Load existing data for catalog and lot with template_id
        function loadExistingData(catalogId, lotNumber, templateId) {
            let apiUrl = `api/get_section_data.php?catalog_id=${catalogId}&template_id=${templateId}`;
            if (lotNumber) {
                apiUrl += `&lot_number=${lotNumber}`;
            }
            
            return fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.message);
                    }
                    
                    catalogData = {};
                    lotData = {};
                    
                    if (data.sections_data) {
                        data.sections_data.forEach(section => {
                            const sectionId = section.section_id;
                            catalogData[sectionId] = {};
                            lotData[sectionId] = {};
                            
                            section.key_values.forEach(kv => {
                                if (kv.source === 'catalog') {
                                    catalogData[sectionId][kv.key] = kv.value;
                                } else if (kv.source === 'lot') {
                                    lotData[sectionId][kv.key] = kv.value;
                                }
                            });
                        });
                    }
                    
                    console.log('Existing data loaded for template', templateId, '- Catalog:', catalogData, 'Lot:', lotData);
                    console.log('API Debug Info:', data.debug_info);
                });
        }

        // Display template-based sections
        function displayTemplateBasedSections() {
            const sections = [1, 2, 3];
            
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = '';
                
                if (templateKeys[sectionId] && templateKeys[sectionId].keys.length > 0) {
                    templateKeys[sectionId].keys.forEach(key => {
                        const keyName = key.key_name;
                        const keySource = key.key_source;
                        
                        // Get existing value
                        let existingValue = '';
                        if (keySource === 'catalog' && catalogData[sectionId] && catalogData[sectionId][keyName]) {
                            existingValue = catalogData[sectionId][keyName];
                        } else if (keySource === 'lot' && lotData[sectionId] && lotData[sectionId][keyName]) {
                            existingValue = lotData[sectionId][keyName];
                        }
                        
                        addTemplateKeyToSection(sectionId, keyName, keySource, existingValue);
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

        // Add template key to section
        function addTemplateKeyToSection(sectionId, keyName, keySource, value) {
            const container = document.getElementById(`keyValues_${sectionId}`);
            const kvId = `kv_${sectionId}_${keyName.replace(/\s+/g, '_')}`;
            
            const sourceColor = keySource === 'catalog' ? 'bg-primary' : 'bg-success';
            const sourceIcon = keySource === 'catalog' ? 'fa-database' : 'fa-tag';
            
            const kvHtml = `
                <div class="key-value-row" id="${kvId}" data-key="${keyName}" data-source="${keySource}">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <label class="form-label">${keyName}</label>
                            <!--<span class="badge ${sourceColor} source-badge ms-2">
                                <i class="fas ${sourceIcon} me-1"></i>
                                ${keySource.charAt(0).toUpperCase() + keySource.slice(1)}
                            </span>-->
                            <strong class="ms-2">${keySource.charAt(0).toUpperCase() + keySource.slice(1)}</strong>
                        </div>
                        <div class="col-md-6">
                            <div class="value-display" id="value_${kvId}">
                                ${value ? `<span class="value-text">${value}</span>` : '<span class="text-muted">No value set</span>'}
                            </div>
                            <div class="value-edit" id="edit_${kvId}" style="display: none;">
                                <textarea class="form-control" rows="2" id="textarea_${kvId}">${value}</textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-sm btn-outline-secondary edit-btn" onclick="editKeyValue('${kvId}')" id="editBtn_${kvId}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-success save-btn" onclick="saveKeyValue('${kvId}')" id="saveBtn_${kvId}" style="display: none;">
                                <i class="fas fa-save"></i> Save
                            </button>
                            <button class="btn btn-sm btn-secondary cancel-btn" onclick="cancelEdit('${kvId}')" id="cancelBtn_${kvId}" style="display: none;">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', kvHtml);
        }

        // Edit key value
        function editKeyValue(kvId) {
            const displayDiv = document.getElementById(`value_${kvId}`);
            const editDiv = document.getElementById(`edit_${kvId}`);
            const editBtn = document.getElementById(`editBtn_${kvId}`);
            const saveBtn = document.getElementById(`saveBtn_${kvId}`);
            const cancelBtn = document.getElementById(`cancelBtn_${kvId}`);
            
            displayDiv.style.display = 'none';
            editDiv.style.display = 'block';
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
            cancelBtn.style.display = 'inline-block';
            
            // Focus on textarea
            const textarea = document.getElementById(`textarea_${kvId}`);
            textarea.focus();
        }

        // Cancel edit
        function cancelEdit(kvId) {
            const displayDiv = document.getElementById(`value_${kvId}`);
            const editDiv = document.getElementById(`edit_${kvId}`);
            const editBtn = document.getElementById(`editBtn_${kvId}`);
            const saveBtn = document.getElementById(`saveBtn_${kvId}`);
            const cancelBtn = document.getElementById(`cancelBtn_${kvId}`);
            const textarea = document.getElementById(`textarea_${kvId}`);
            
            // Reset textarea to original value
            const keyValueRow = document.getElementById(kvId);
            const keyName = keyValueRow.getAttribute('data-key');
            const keySource = keyValueRow.getAttribute('data-source');
            const sectionId = kvId.split('_')[1];
            
            let originalValue = '';
            if (keySource === 'catalog' && catalogData[sectionId] && catalogData[sectionId][keyName]) {
                originalValue = catalogData[sectionId][keyName];
            } else if (keySource === 'lot' && lotData[sectionId] && lotData[sectionId][keyName]) {
                originalValue = lotData[sectionId][keyName];
            }
            
            textarea.value = originalValue;
            
            displayDiv.style.display = 'block';
            editDiv.style.display = 'none';
            editBtn.style.display = 'inline-block';
            saveBtn.style.display = 'none';
            cancelBtn.style.display = 'none';
        }

        // Save key value
        function saveKeyValue(kvId) {
            const keyValueRow = document.getElementById(kvId);
            const keyName = keyValueRow.getAttribute('data-key');
            const keySource = keyValueRow.getAttribute('data-source');
            const sectionId = kvId.split('_')[1];
            const textarea = document.getElementById(`textarea_${kvId}`);
            const newValue = textarea.value.trim();
            
            // Save to database
            const payload = {
                catalog_id: currentCatalogId,
                section_id: sectionId,
                key: keyName,
                value: newValue,
                source: keySource
            };
            
            if (keySource === 'lot') {
                payload.lot_number = currentLotNumber;
            }
            
            // Show saving state
            const saveBtn = document.getElementById(`saveBtn_${kvId}`);
            const originalSaveText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            saveBtn.disabled = true;
            
            fetch('api/save_key_value_pair.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update local data
                    if (keySource === 'catalog') {
                        if (!catalogData[sectionId]) catalogData[sectionId] = {};
                        catalogData[sectionId][keyName] = newValue;
                    } else if (keySource === 'lot') {
                        if (!lotData[sectionId]) lotData[sectionId] = {};
                        lotData[sectionId][keyName] = newValue;
                    }
                    
                    // Update display
                    const displayDiv = document.getElementById(`value_${kvId}`);
                    displayDiv.innerHTML = newValue ? `<span class="value-text">${newValue}</span>` : '<span class="text-muted">No value set</span>';
                    
                    // Exit edit mode
                    const editDiv = document.getElementById(`edit_${kvId}`);
                    const editBtn = document.getElementById(`editBtn_${kvId}`);
                    const cancelBtn = document.getElementById(`cancelBtn_${kvId}`);
                    
                    displayDiv.style.display = 'block';
                    editDiv.style.display = 'none';
                    editBtn.style.display = 'inline-block';
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';
                    
                    console.log('Value saved successfully:', data);
                    
                    // Show success feedback
                    showSaveSuccessFeedback(kvId);
                    
                } else {
                    alert('Error saving value: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error saving value:', error);
                alert('Error saving value. Please try again.');
            })
            .finally(() => {
                // Reset button
                saveBtn.innerHTML = originalSaveText;
                saveBtn.disabled = false;
            });
        }

        // Show save success feedback
        function showSaveSuccessFeedback(kvId) {
            const editBtn = document.getElementById(`editBtn_${kvId}`);
            const originalContent = editBtn.innerHTML;
            
            editBtn.innerHTML = '<i class="fas fa-check text-success"></i> Saved';
            editBtn.classList.add('btn-outline-success');
            editBtn.classList.remove('btn-outline-secondary');
            
            setTimeout(() => {
                editBtn.innerHTML = originalContent;
                editBtn.classList.remove('btn-outline-success');
                editBtn.classList.add('btn-outline-secondary');
            }, 2000);
        }

        // Show loading state in sections
        function showLoadingInSections() {
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Loading template data...
                    </div>
                `;
            });
        }

        // Show error in sections
        function showErrorInSections(errorMessage) {
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = `
                    <div class="text-center py-3 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error: ${errorMessage}
                    </div>
                `;
            });
        }

        // Create new CoA (with template structure)
        function createNewCoA() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            const templateId = document.getElementById('templateSelect').value;
            
            if (!templateId) {
                alert('Please select a template first');
                return;
            }
            
            if (!catalogId) {
                alert('Please select a catalog');
                return;
            }
            
            if (!catalogName) {
                alert('Please enter a catalog name');
                return;
            }
            
            if (!lotNumber) {
                alert('Please select or create a lot');
                return;
            }

            currentCatalogId = catalogId;
            currentLotNumber = lotNumber;
            currentTemplateId = templateId;
            
            const selectedOption = document.getElementById('catalogSelect').options[document.getElementById('catalogSelect').selectedIndex];
            const catalogNumber = selectedOption.getAttribute('data-catalog-number');
            
            // Update header display
            document.getElementById('catalogTitle').textContent = catalogName;
            document.getElementById('displayCatalogNumber').textContent = catalogNumber;
            document.getElementById('displayLotNumber').textContent = lotNumber;
            updateTemplateDisplay();

            showLoadingInSections();
            
            // Load template structure and create empty CoA
            loadTemplateStructure(templateId)
                .then(() => {
                    // Initialize empty data
                    catalogData = {};
                    lotData = {};
                    
                    displayTemplateBasedSections();
                    document.getElementById('previewBtn').disabled = false;
                    document.getElementById('generateBtn').disabled = false;
                    
                    alert('New CoA template loaded! You can now edit the values for each key.');
                })
                .catch(error => {
                    console.error('Error creating new CoA:', error);
                    showErrorInSections(error.message);
                });
        }

        // Clear all data and reset to initial state
        function clearAllData() {
            if (confirm('Are you sure you want to clear all data and reset the form?')) {
                // Reset form selections
                document.getElementById('catalogSelect').selectedIndex = 0;
                
                const catalogNameField = document.getElementById('catalogName');
                catalogNameField.value = '';
                catalogNameField.readOnly = true;
                catalogNameField.placeholder = 'Enter catalog name';
                catalogNameField.classList.remove('border-warning');
                
                const lotSelect = document.getElementById('lotSelect');
                lotSelect.innerHTML = '<option value="">Select Lot...</option>';
                lotSelect.disabled = true;
                
                // Reset header display
                document.getElementById('catalogTitle').textContent = 'Select a catalog to begin';
                document.getElementById('displayCatalogNumber').textContent = 'Not selected';
                document.getElementById('displayLotNumber').textContent = 'Not selected';
                document.getElementById('displayTemplate').textContent = 'Not selected';
                
                // Reset sections
                resetSectionsToDefault();
                
                // Reset global variables
                currentCatalogId = null;
                currentLotNumber = null;
                currentTemplateId = null;
                templateKeys = {};
                catalogData = {};
                lotData = {};
                sectionsData = {};
                
                // Disable buttons
                document.getElementById('loadDataBtn').disabled = true;
                document.getElementById('createNewBtn').disabled = true;
                document.getElementById('addLotBtn').disabled = true;
                document.getElementById('previewBtn').disabled = true;
                document.getElementById('generateBtn').disabled = true;
                
                console.log('Data cleared successfully');
            }
        }

        // Reset sections to default empty state
        function resetSectionsToDefault() {
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = `
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Select template, catalog and lot, then click "Load Data" to view template
                    </div>
                `;
            });
        }

        // Template Management Functions
        function initializeTemplateManagementHandlers() {
            const manageTemplateBtn = document.getElementById('manageTemplateBtn');
            if (manageTemplateBtn) {
                manageTemplateBtn.addEventListener('click', function() {
                    openTemplateManagement();
                });
            }

            const createTemplateBtn = document.getElementById('createTemplateBtn');
            if (createTemplateBtn) {
                createTemplateBtn.addEventListener('click', function() {
                    createNewTemplate();
                });
            }

           // Event delegation for adding keys
            const templateManagementModal = document.getElementById('templateManagementModal');
            if (templateManagementModal) {
                templateManagementModal.addEventListener('click', function(event) {
                if (event.target.classList.contains('add-key-btn')) {
                    addKeyField(event);
                }
            });
            }

            // Event delegation for removing keys
            document.querySelector('#templateManagementModal').addEventListener('click', function(event) {if (event.target.classList.contains('remove-key-btn')) {
                removeKeyField(event);}});
        }

        // Template Configuration Functions
        function initializeTemplateConfigHandlers() {
            const saveTemplateKeyBtn = document.getElementById('saveTemplateKeyBtn');
            if (saveTemplateKeyBtn) {
                saveTemplateKeyBtn.addEventListener('click', function() {
                    saveTemplateKey();
                });
            }
            
            const saveTemplateConfigBtn = document.getElementById('saveTemplateConfigBtn');
            if (saveTemplateConfigBtn) {
                saveTemplateConfigBtn.addEventListener('click', function() {
                    bootstrap.Modal.getInstance(document.getElementById('templateConfigModal')).hide();
                    alert('Template configuration saved successfully!');
                });
            }
        }

        // Open template management modal
        function openTemplateManagement() {
            loadTemplatesList();
            new bootstrap.Modal(document.getElementById('templateManagementModal')).show();
        }

        // Load templates list for management
        function loadTemplatesList() {
            const container = document.getElementById('templatesListContainer');
            container.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Loading templates...
                </div>
            `;

            fetch('api/get_templates.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.templates && data.templates.length > 0) {
                        let templatesHtml = '';
                        
                        data.templates.forEach(template => {
                            const isDefault = template.is_default;
                            const defaultBadge = isDefault ? '<span class="badge bg-primary ms-2">Default</span>' : '';
                            
                            templatesHtml += `
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-6">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-file-alt me-2"></i>
                                                    ${template.template_name}
                                                    ${defaultBadge}
                                                </h6>
                                                <small class="text-muted">${template.description || 'No description'}</small>
                                            </div>
                                            <div class="col-md-6 text-end">
                                                <div class="btn-group">
                                                    ${!isDefault ? `
                                                        <button class="btn btn-sm btn-outline-primary" onclick="setDefaultTemplate(${template.id})">
                                                            <i class="fas fa-star"></i> Set Default
                                                        </button>
                                                    ` : ''}
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="configureTemplateKeys(${template.id}, '${template.template_name}')">
                                                        <i class="fas fa-cogs"></i> Configure
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplate(${template.id}, '${template.template_name}')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        container.innerHTML = templatesHtml;
                    } else {
                        container.innerHTML = `
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No templates found. Create your first template above.</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading templates:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading templates. Please try again.
                        </div>
                    `;
                });
        }

        // Create new template
        function createNewTemplate() {
            const templateName = document.getElementById('newTemplateName').value.trim();
            const description = document.getElementById('newTemplateDescription').value.trim();
            const isDefault = document.getElementById('setAsDefault').checked;


            if (!templateName) {
                alert('Please enter a template name');
                return;
            }

            // Collect initial keys for each section
            const initialKeys = {
                description: getKeysForSection('description'),
                specifications: getKeysForSection('specifications'),
                preparation: getKeysForSection('preparation')
            };

            const payload = {
                template_name: templateName,
                description: description,
                is_default: isDefault,
                initial_keys: initialKeys
            };

            // Function to retrieve keys for a given section
            function getKeysForSection(section) {
                const containerId = `${section}KeysContainer`;
                const container = document.getElementById(containerId);
                const keys = [];

                if (container) {
                    const keyRows = container.querySelectorAll('.key-row');

                    keyRows.forEach(row => {
                        const nameInput = row.querySelector('.key-name');
                        const typeSelect = row.querySelector('.key-type');

                        if (nameInput && typeSelect) {
                            const keyName = nameInput.value.trim();
                            const keyType = typeSelect.value;

                            if (keyName) {
                                let section_id;
                                switch (section) {
                                    case 'description':
                                        section_id = 1;
                                        break;
                                    case 'specifications':
                                        section_id = 2;
                                        break;
                                    case 'preparation':
                                        section_id = 3;
                                        break;
                                    default:
                                        section_id = 1;
                                }
                                keys.push({ name: keyName, type: keyType, section_id: section_id });
                            }
                        }
                    });
                }

                return keys;

            };

            fetch('api/create_template.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('newTemplateName').value = '';
                    document.getElementById('newTemplateDescription').value = '';
                    document.getElementById('setAsDefault').checked = false;

                    loadTemplatesList();
                    loadTemplates();

                    alert('Template created successfully! Click "Configure" to add keys to each section.');
                } else {
                    alert('Error creating template: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error creating template:', error);
                alert('Error creating template. Please try again.');
            });
        }

        // Set template as default
        function setDefaultTemplate(templateId) {
            if (!confirm('Are you sure you want to set this template as default?')) {
                return;
            }

            fetch('api/set_default_template.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ template_id: templateId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTemplatesList();
                    loadTemplates();
                    alert('Default template updated successfully!');
                } else {
                    alert('Error setting default template: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error setting default template:', error);
                alert('Error setting default template. Please try again.');
            });
        }

        // Delete template
        function deleteTemplate(templateId, templateName) {
            if (!confirm(`Are you sure you want to delete the template "${templateName}"? This action cannot be undone.`)) {
                return;
            }

            fetch('api/delete_template.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ template_id: templateId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTemplatesList();
                    loadTemplates();
                    alert('Template deleted successfully!');
                } else {
                    alert('Error deleting template: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error deleting template:', error);
                alert('Error deleting template. Please try again.');
            });
        }

        // Configure template keys
        function configureTemplateKeys(templateId, templateName) {
            currentTemplateId = templateId;
            
            document.getElementById('templateConfigModalTitle').innerHTML = `
                <i class="fas fa-cogs me-2"></i>
                Configure: ${templateName}
            `;
            
            loadTemplateConfiguration(templateId);
            new bootstrap.Modal(document.getElementById('templateConfigModal')).show();
        }

        // Load template configuration
        function loadTemplateConfiguration(templateId) {
            const container = document.getElementById('templateConfigSections');
            const infoContainer = document.getElementById('configTemplateInfo');
            
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading template configuration...</p>
                </div>
            `;
            
            fetch(`api/get_template_keys.php?template_id=${templateId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        infoContainer.innerHTML = `
                            <strong>${data.template_info.template_name}</strong>
                            ${data.template_info.description ? ` - ${data.template_info.description}` : ''}
                        `;
                        
                        let sectionsHtml = '';
                        
                        data.sections.forEach(section => {
                            sectionsHtml += `
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">
                                                <i class="fas fa-folder me-2"></i>
                                                ${section.section_name}
                                            </h6>
                                            <button class="btn btn-sm btn-primary" onclick="addKeyToSection(${section.section_id}, '${section.section_name}')">
                                                <i class="fas fa-plus me-1"></i>
                                                Add Key
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="keys-container" id="section_${section.section_id}_keys">
                                            ${section.keys.length === 0 ? `
                                                <div class="text-muted text-center py-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    No keys defined for this section. Click "Add Key" to start.
                                                </div>
                                            ` : ''}
                                            ${section.keys.map(key => createKeyRowHtml(key)).join('')}
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        container.innerHTML = sectionsHtml;
                    } else {
                        container.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error loading template configuration: ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error loading template configuration:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading template configuration. Please try again.
                        </div>
                    `;
                });
        }

        // Create HTML for a key row
        function createKeyRowHtml(key) {
            const sourceColor = key.key_source === 'catalog' ? 'bg-primary' : 'bg-success';
            const sourceIcon = key.key_source === 'catalog' ? 'fa-database' : 'fa-tag';
            
            return `
                <div class="key-row border rounded p-3 mb-2" data-key-id="${key.id}">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-grip-vertical text-muted me-2" style="cursor: move;"></i>
                                <div>
                                    <strong>${key.key_name}</strong><br>
                                    <small class="text-muted">Order: ${key.key_order}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <span class="badge ${sourceColor}">
                                <i class="fas ${sourceIcon} me-1"></i>
                                ${key.key_source.charAt(0).toUpperCase() + key.key_source.slice(1)} Data
                            </span>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteTemplateKey(${key.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Add key to section
        function addKeyToSection(sectionId, sectionName) {
            currentSectionIdForKey = sectionId;
            
            document.getElementById('templateKeyName').value = '';
            document.getElementById('templateKeySource').value = 'catalog';
            
            document.querySelector('#addTemplateKeyModal .modal-title').textContent = `Add Key to ${sectionName}`;
            
            new bootstrap.Modal(document.getElementById('addTemplateKeyModal')).show();
        }

        // Save template key
        function saveTemplateKey() {
            const keyName = document.getElementById('templateKeyName').value.trim();
            const keySource = document.getElementById('templateKeySource').value;
            
            if (!keyName) {
                alert('Please enter a key name');
                return;
            }
            
            if (!currentTemplateId || !currentSectionIdForKey) {
                alert('Error: Missing template or section information');
                return;
            }
            
            const payload = {
                template_id: currentTemplateId,
                section_id: currentSectionIdForKey,
                key_name: keyName,
                key_source: keySource
            };
            
            fetch('api/add_template_key.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addTemplateKeyModal')).hide();
                    loadTemplateConfiguration(currentTemplateId);
                    console.log('Template key added successfully');
                } else {
                    alert('Error adding template key: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error adding template key:', error);
                alert('Error adding template key. Please try again.');
            });
        }

        // Delete template key
        function deleteTemplateKey(keyId) {
            if (!confirm('Are you sure you want to delete this key from the template?')) {
                return;
            }
            
            fetch('api/delete_template_key.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ key_id: keyId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadTemplateConfiguration(currentTemplateId);
                    console.log('Template key deleted successfully');
                } else {
                    alert('Error deleting template key: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error deleting template key:', error);
                alert('Error deleting template key. Please try again.');
            });
        }

        // Event listeners
        function initializeEventListeners() {
            // Catalog selection
            const catalogSelect = document.getElementById('catalogSelect');
            if (catalogSelect) {
                catalogSelect.addEventListener('change', function() {
                    const catalogId = this.value;
                    if (catalogId) {
                        const selectedOption = this.options[this.selectedIndex];
                        const catalogName = selectedOption.getAttribute('data-catalog-name');
                        const catalogNumber = selectedOption.getAttribute('data-catalog-number');
                        
                        const catalogNameField = document.getElementById('catalogName');
                        catalogNameField.value = catalogName || '';
                        catalogNameField.readOnly = !!catalogName;
                        
                        if (!catalogName) {
                            catalogNameField.placeholder = 'Enter catalog name for ' + catalogNumber;
                            catalogNameField.classList.add('border-warning');
                        } else {
                            catalogNameField.classList.remove('border-warning');
                        }
                        
                        loadLots(catalogId);
                        loadCatalogTemplate(catalogId);
                        
                        document.getElementById('loadDataBtn').disabled = false;
                        document.getElementById('createNewBtn').disabled = false;
                        document.getElementById('addLotBtn').disabled = false;
                    } else {
                        const catalogNameField = document.getElementById('catalogName');
                        catalogNameField.value = '';
                        catalogNameField.readOnly = true;
                        catalogNameField.placeholder = 'Enter catalog name';
                        catalogNameField.classList.remove('border-warning');
                        
                        document.getElementById('catalogTitle').textContent = 'Select a catalog to begin';
                        document.getElementById('displayCatalogNumber').textContent = 'Not selected';
                        document.getElementById('displayLotNumber').textContent = 'Not selected';
                        document.getElementById('displayTemplate').textContent = 'Not selected';
                        
                        document.getElementById('lotSelect').disabled = true;
                        document.getElementById('loadDataBtn').disabled = true;
                        document.getElementById('createNewBtn').disabled = true;
                        document.getElementById('addLotBtn').disabled = true;
                        
                        resetSectionsToDefault();
                    }
                });
            }

            // Catalog name field change
            const catalogName = document.getElementById('catalogName');
            if (catalogName) {
                catalogName.addEventListener('blur', function() {
                    const catalogId = document.getElementById('catalogSelect').value;
                    const newCatalogName = this.value.trim();
                    
                    if (catalogId && newCatalogName && !this.readOnly) {
                        updateCatalogName(catalogId, newCatalogName);
                    }
                });
            }

            // Add catalog button
            const addCatalogBtn = document.getElementById('addCatalogBtn');
            if (addCatalogBtn) {
                addCatalogBtn.addEventListener('click', function() {
                    loadTemplatesIntoModal();
                    new bootstrap.Modal(document.getElementById('addCatalogModal')).show();
                });
            }

            // Add lot button
            const addLotBtn = document.getElementById('addLotBtn');
            if (addLotBtn) {
                addLotBtn.addEventListener('click', function() {
                    const catalogSelect = document.getElementById('catalogSelect');
                    const selectedOption = catalogSelect.options[catalogSelect.selectedIndex];
                    const catalogName = document.getElementById('catalogName').value || 'Unnamed Product';
                    const catalogNumber = selectedOption.getAttribute('data-catalog-number');
                    document.getElementById('selectedCatalogInfo').value = `${catalogName} (${catalogNumber})`;
                    new bootstrap.Modal(document.getElementById('addLotModal')).show();
                });
            }

            // Save catalog button
            const saveCatalogBtn = document.getElementById('saveCatalogBtn');
            if (saveCatalogBtn) {
                saveCatalogBtn.addEventListener('click', saveNewCatalog);
            }

            // Save lot button
            const saveLotBtn = document.getElementById('saveLotBtn');
            if (saveLotBtn) {
                saveLotBtn.addEventListener('click', saveNewLot);
            }

            // Load data button
            const loadDataBtn = document.getElementById('loadDataBtn');
            if (loadDataBtn) {
                loadDataBtn.addEventListener('click', loadData);
            }

            // Create new CoA button
            const createNewBtn = document.getElementById('createNewBtn');
            if (createNewBtn) {
                createNewBtn.addEventListener('click', createNewCoA);
            }

            // Clear data button
            const clearDataBtn = document.getElementById('clearDataBtn');
            if (clearDataBtn) {
                clearDataBtn.addEventListener('click', clearAllData);
            }

            // Generate PDF button
            const generateBtn = document.getElementById('generateBtn');
            if (generateBtn) {
                generateBtn.addEventListener('click', generatePDF);
            }

            // Preview PDF button
            const previewBtn = document.getElementById('previewBtn');
            if (previewBtn) {
                previewBtn.addEventListener('click', previewPDF);
            }
        }

        // Template-related functions
        function initializeTemplateHandlers() {
            const templateSelect = document.getElementById('templateSelect');
            if (templateSelect) {
                templateSelect.addEventListener('change', function() {
                    const templateId = this.value;
                    const catalogId = document.getElementById('catalogSelect').value;
                    
                    if (templateId && catalogId) {
                        updateCatalogTemplate(catalogId, templateId);
                    }
                    
                    updateTemplateDisplay();
                });
            }
        }

        function loadTemplates() {
            fetch('api/get_templates.php')
                .then(response => response.json())
                .then(data => {
                    const templateSelect = document.getElementById('templateSelect');
                    templateSelect.innerHTML = '<option value="">Select Template...</option>';
                    
                    if (data.success && data.templates && data.templates.length > 0) {
                        data.templates.forEach(template => {
                            const option = document.createElement('option');
                            option.value = template.id;
                            option.textContent = template.template_name;
                            if (template.is_default) {
                                option.textContent += ' (Default)';
                                option.selected = true;
                            }
                            templateSelect.appendChild(option);
                        });
                        
                        updateTemplateDisplay();
                    } else {
                        templateSelect.innerHTML += '<option value="" disabled>No templates found</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading templates:', error);
                    const templateSelect = document.getElementById('templateSelect');
                    templateSelect.innerHTML = '<option value="" disabled>Error loading templates</option>';
                });
        }

        function loadTemplatesIntoModal() {
            fetch('api/get_templates.php')
                .then(response => response.json())
                .then(data => {
                    const modalTemplateSelect = document.getElementById('catalogTemplateSelect');
                    modalTemplateSelect.innerHTML = '<option value="">Use default template</option>';
                    
                    if (data.success && data.templates && data.templates.length > 0) {
                        data.templates.forEach(template => {
                            const option = document.createElement('option');
                            option.value = template.id;
                            option.textContent = template.template_name;
                            if (template.is_default) {
                                option.textContent += ' (Default)';
                            }
                            modalTemplateSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading templates into modal:', error);
                });
        }

        function loadCatalogTemplate(catalogId) {
            fetch(`api/get_catalog_template.php?catalog_id=${catalogId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.template_id) {
                        const templateSelect = document.getElementById('templateSelect');
                        templateSelect.value = data.template_id;
                        updateTemplateDisplay();
                    }
                })
                .catch(error => {
                    console.error('Error loading catalog template:', error);
                });
        }

        function updateCatalogTemplate(catalogId, templateId) {
            fetch('api/update_catalog_template.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    catalog_id: catalogId,
                    template_id: templateId 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Catalog template updated successfully');
                } else {
                    console.error('Error updating catalog template:', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating catalog template:', error);
            });
        }

        function updateTemplateDisplay() {
            const templateSelect = document.getElementById('templateSelect');
            const selectedOption = templateSelect.options[templateSelect.selectedIndex];
            const displayTemplate = document.getElementById('displayTemplate');
            
            if (selectedOption && selectedOption.value) {
                displayTemplate.textContent = selectedOption.textContent;
            } else {
                displayTemplate.textContent = 'Not selected';
            }
        }

        // Load catalogs from database
        function loadCatalogs() {
            fetch('api/get_catalog_data.php')
                .then(response => response.json())
                .then(data => {
                    const catalogSelect = document.getElementById('catalogSelect');
                    catalogSelect.innerHTML = '<option value="">Select Catalog...</option>';
                    
                    if (data.length === 0) {
                        catalogSelect.innerHTML += '<option value="" disabled>No catalogs found - Click + to add one</option>';
                    } else {
                        data.forEach(catalog => {
                            const option = document.createElement('option');
                            option.value = catalog.id;
                            option.textContent = catalog.catalog_number;
                            option.setAttribute('data-catalog-name', catalog.catalog_name || '');
                            option.setAttribute('data-catalog-number', catalog.catalog_number);
                            catalogSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading catalogs:', error);
                    const catalogSelect = document.getElementById('catalogSelect');
                    catalogSelect.innerHTML = '<option value="" disabled>Error loading catalogs</option>';
                });
        }

        // Load lots for selected catalog
        function loadLots(catalogId) {
            fetch(`api/get_lot_data.php?catalog_id=${catalogId}`)
                .then(response => response.json())
                .then(data => {
                    const lotSelect = document.getElementById('lotSelect');
                    lotSelect.innerHTML = '<option value="">Select Lot...</option>';
                    lotSelect.disabled = false;
                    
                    if (data.length === 0) {
                        lotSelect.innerHTML += '<option value="" disabled>No lots found - Click + to add one</option>';
                    } else {
                        data.forEach(lot => {
                            const option = document.createElement('option');
                            option.value = lot.lot_number;
                            option.textContent = lot.lot_number;
                            lotSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading lots:', error);
                    const lotSelect = document.getElementById('lotSelect');
                    lotSelect.innerHTML = '<option value="" disabled>Error loading lots</option>';
                });
        }

        // Save new catalog
        function saveNewCatalog() {
            const catalogName = document.getElementById('newCatalogName').value.trim();
            const catalogNumber = document.getElementById('newCatalogNumber').value.trim();
            const templateId = document.getElementById('catalogTemplateSelect').value;
            
            if (!catalogName) {
                alert('Please enter a catalog name');
                return;
            }
            
            if (!catalogNumber) {
                alert('Please enter a catalog number');
                return;
            }

            const payload = { 
                catalog_name: catalogName,
                catalog_number: catalogNumber 
            };
            
            if (templateId) {
                payload.template_id = parseInt(templateId);
            }

            fetch('api/save_catalog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCatalogs();
                    
                    setTimeout(() => {
                        document.getElementById('catalogSelect').value = data.catalog_id;
                        document.getElementById('catalogSelect').dispatchEvent(new Event('change'));
                    }, 100);
                    
                    document.getElementById('newCatalogName').value = '';
                    document.getElementById('newCatalogNumber').value = '';
                    document.getElementById('catalogTemplateSelect').value = '';
                    bootstrap.Modal.getInstance(document.getElementById('addCatalogModal')).hide();
                    
                    if (data.action === 'created') {
                        alert('New catalog created successfully!');
                    } else if (data.action === 'updated') {
                        alert('Catalog updated successfully!');
                    } else if (data.action === 'existing') {
                        alert('Catalog already exists and has been selected.');
                    }
                } else {
                    alert('Error adding catalog: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error saving catalog:', error);
                alert('Error saving catalog. Please try again.');
            });
        }

        // Save new lot
        function saveNewLot() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('newLotNumber').value.trim();
            
            if (!catalogId) {
                alert('Please select a catalog first');
                return;
            }
            
            if (!lotNumber) {
                alert('Please enter a lot number');
                return;
            }

            fetch('api/save_lot.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    catalog_id: catalogId,
                    lot_number: lotNumber 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadLots(catalogId);
                    
                    setTimeout(() => {
                        document.getElementById('lotSelect').value = lotNumber;
                    }, 100);
                    
                    document.getElementById('newLotNumber').value = '';
                    bootstrap.Modal.getInstance(document.getElementById('addLotModal')).hide();
                    
                    if (data.action === 'created') {
                        alert('New lot created successfully!');
                    } else if (data.action === 'existing') {
                        alert('Lot already exists and has been selected.');
                    } else {
                        alert('Lot added successfully!');
                    }
                } else {
                    alert('Error adding lot: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error saving lot:', error);
                alert('Error saving lot. Please try again.');
            });
        }

        // Update catalog name in database
        function updateCatalogName(catalogId, catalogName) {
            fetch('api/update_catalog_name.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    catalog_id: catalogId,
                    catalog_name: catalogName 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const catalogSelect = document.getElementById('catalogSelect');
                    const selectedOption = catalogSelect.options[catalogSelect.selectedIndex];
                    selectedOption.setAttribute('data-catalog-name', catalogName);
                    
                    const catalogNameField = document.getElementById('catalogName');
                    catalogNameField.readOnly = true;
                    catalogNameField.classList.remove('border-warning');
                    
                    console.log('Catalog name updated successfully');
                } else {
                    alert('Error updating catalog name: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error updating catalog name:', error);
                alert('Error updating catalog name. Please try again.');
            });
        }

        function addKeyField(button) {
            const section = button.dataset.section;
            const containerId = `${section}KeysContainer`;
            const container = document.getElementById(containerId);

            if (container) {
                const keyRow = document.createElement('div');
                keyRow.classList.add('input-group', 'mb-2', 'key-row');

                keyRow.innerHTML = `
                    <input type="text" class="form-control key-name" placeholder="Key Name">
                    <select class="form-select key-type">
                        <option value="catalog">Catalog</option>
                        <option value="lot">Lot</option>
                    </select>
                    <button class="btn btn-outline-danger remove-key-btn" type="button">
                        <i class="fas fa-trash"></i>
                    </button>
                `;

                container.appendChild(keyRow);
            }
        }

        function removeKeyField(button) {
            const keyRow = button.closest('.key-row');
            if (keyRow) {
                keyRow.remove();
            }
        }

        // Generate PDF
        function generatePDF() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            
            if (!catalogId) {
                alert('Please select a catalog first');
                return;
            }
            
            if (!lotNumber) {
                alert('Please select a lot number');
                return;
            }
            
            if (!catalogName) {
                alert('Catalog name is required for PDF generation');
                return;
            }

            const generateUrl = `api/generate_pdf.php?catalog_id=${catalogId}&lot_number=${encodeURIComponent(lotNumber)}`;
            window.open(generateUrl, '_blank');
        }

        // Preview PDF
        function previewPDF() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            
            if (!catalogId) {
                alert('Please select a catalog first');
                return;
            }
            
            if (!lotNumber) {
                alert('Please select a lot number');
                return;
            }
            
            if (!catalogName) {
                alert('Catalog name is required for PDF preview');
                return;
            }

            const previewUrl = `api/preview_pdf.php?catalog_id=${catalogId}&lot_number=${encodeURIComponent(lotNumber)}`;
            window.open(previewUrl, '_blank');
        }
    </script>

</body>
</html>