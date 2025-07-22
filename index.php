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
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .section-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
            cursor: pointer;
        }
        .section-content {
            padding: 20px;
        }
        .key-value-row {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
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

    <div class="container mt-4">
        <!-- Selection Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="templateSelect" class="form-label">Template</label>
                                <div class="input-group">
                                    <select class="form-select" id="templateSelect" disabled>
                                        <option value="">Select Template...</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" id="manageTemplateBtn" disabled>
                                        <i class="fas fa-cog"></i>
                                    </button>
                                </div>
                            </div>
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
                            <div class="col-md-4">
                                <label for="catalogName" class="form-label">Catalog Name</label>
                                <input type="text" class="form-control" id="catalogName" placeholder="Enter catalog name" readonly>
                            </div>
                            <div class="col-md-4">
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
        <div class="row mb-4" id="certificateHeader">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h2 class="mb-3" id="catalogTitle">Select a catalog to begin</h2>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Catalog Number:</strong> <span id="displayCatalogNumber">Not selected</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Lot Number:</strong> <span id="displayLotNumber">Not selected</span>
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
                            Select catalog and lot, then click "Load Data" to view content
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="addKeyValue('1')" disabled>
                            <i class="fas fa-plus"></i> Add Key-Value
                        </button>
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
                            Select catalog and lot, then click "Load Data" to view content
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="addKeyValue('2')" disabled>
                            <i class="fas fa-plus"></i> Add Key-Value
                        </button>
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
                            Select catalog and lot, then click "Load Data" to view content
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="addKeyValue('3')" disabled>
                            <i class="fas fa-plus"></i> Add Key-Value
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-4 mb-5">
            <div class="col-12 text-center">
                <button class="btn btn-primary btn-lg me-3" id="previewBtn" disabled>
                    <i class="fas fa-eye me-2"></i>
                    Preview PDF
                </button>
                <button class="btn btn-success btn-lg" id="generateBtn" disabled>
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

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="sectionName" class="form-label">Section Name</label>
                        <input type="text" class="form-control" id="sectionName" placeholder="Enter section name">
                    </div>
                    <div class="mb-3">
                        <label for="sectionDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="sectionDescription" rows="3" placeholder="Enter section description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSectionBtn">Add Section</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Key-Value Modal -->
    <div class="modal fade" id="addKeyValueModal" tabindex="-1" aria-labelledby="addKeyValueModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addKeyValueModalLabel">Add Key-Value Pair</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="keySource" class="form-label">Data Source</label>
                        <select class="form-select" id="keySource">
                            <option value="catalog">Catalog Data</option>
                            <option value="lot">Lot Data</option>
                        </select>
                    </div>
                    <div class="mb-3" id="existingKeyGroup">
                        <label for="existingKey" class="form-label">Select Existing Key</label>
                        <select class="form-select" id="existingKey">
                            <option value="">Select existing key...</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customKeyGroup">
                        <label for="customKey" class="form-label">Or Enter New Key</label>
                        <input type="text" class="form-control" id="customKey" placeholder="Enter new key name">
                    </div>
                    <div class="mb-3">
                        <label for="keyValue" class="form-label">Value</label>
                        <textarea class="form-control" id="keyValue" rows="3" placeholder="Enter value"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveKeyValueBtn">Add Key-Value</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    
    <script>
        // Global variables
        let currentSectionId = null;
        let sectionsData = {};
        let sectionCounter = 0;

        // Clear all data and reset to initial state
        function clearAllData() {
            // Confirm action
            if (confirm('Are you sure you want to clear all data and reset the form? This action cannot be undone.')) {
                // Reset catalog selection
                document.getElementById('catalogSelect').selectedIndex = 0;
                
                // Reset catalog name field
                const catalogNameField = document.getElementById('catalogName');
                catalogNameField.value = '';
                catalogNameField.readOnly = true;
                catalogNameField.placeholder = 'Enter catalog name';
                catalogNameField.classList.remove('border-warning');
                
                // Reset lot selection
                const lotSelect = document.getElementById('lotSelect');
                lotSelect.innerHTML = '<option value="">Select Lot...</option>';
                lotSelect.disabled = true;
                
                // Reset header to initial state
                document.getElementById('catalogTitle').textContent = 'Select a catalog to begin';
                document.getElementById('displayCatalogNumber').textContent = 'Not selected';
                document.getElementById('displayLotNumber').textContent = 'Not selected';
                
                // Reset all sections to default state
                resetSectionsToDefault();
                
                // Disable all action buttons
                document.getElementById('loadDataBtn').disabled = true;
                document.getElementById('createNewBtn').disabled = true;
                document.getElementById('addLotBtn').disabled = true;
                document.getElementById('previewBtn').disabled = true;
                document.getElementById('generateBtn').disabled = true;
                
                // Clear sections data
                sectionsData = {};
                
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
                        Select catalog and lot, then click "Load Data" to view content
                    </div>
                `;
                
                // Disable add key-value buttons
                const addButton = container.parentElement.querySelector('.btn-outline-primary');
                if (addButton) {
                    addButton.disabled = true;
                }
            });
            
            // Reset sections data
            sectionsData = {};
        }

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            loadCatalogs();
            initializeEventListeners();
            
            // Initialize sections data and disable buttons on page load
            sectionsData = {};
            document.getElementById('previewBtn').disabled = true;
            document.getElementById('generateBtn').disabled = true;
        });

        // Event listeners
        function initializeEventListeners() {
            // Catalog selection
            document.getElementById('catalogSelect').addEventListener('change', function() {
                const catalogId = this.value;
                if (catalogId) {
                    // Update catalog name field and header
                    const selectedOption = this.options[this.selectedIndex];
                    const catalogName = selectedOption.getAttribute('data-catalog-name');
                    const catalogNumber = selectedOption.getAttribute('data-catalog-number');
                    
                    // Update the catalog name input field
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
                    loadTemplates(); // Load available templates
                    loadCatalogTemplate(catalogId); // Load catalog's current template
                    
                    document.getElementById('templateSelect').disabled = false;
                    document.getElementById('manageTemplateBtn').disabled = false;
                    document.getElementById('loadDataBtn').disabled = false;
                    document.getElementById('createNewBtn').disabled = false;
                    document.getElementById('addLotBtn').disabled = false;
                } else {
                    // Clear and disable everything
                    const catalogNameField = document.getElementById('catalogName');
                    catalogNameField.value = '';
                    catalogNameField.readOnly = true;
                    catalogNameField.placeholder = 'Enter catalog name';
                    catalogNameField.classList.remove('border-warning');
                    
                    // Reset header to default state
                    document.getElementById('catalogTitle').textContent = 'Select a catalog to begin';
                    document.getElementById('displayCatalogNumber').textContent = 'Not selected';
                    document.getElementById('displayLotNumber').textContent = 'Not selected';
                    
                    document.getElementById('lotSelect').disabled = true;
                    document.getElementById('templateSelect').disabled = true;
                    document.getElementById('manageTemplateBtn').disabled = true;
                    document.getElementById('loadDataBtn').disabled = true;
                    document.getElementById('createNewBtn').disabled = true;
                    document.getElementById('addLotBtn').disabled = true;
                    
                    // Reset sections to default state
                    resetSectionsToDefault();
                }
            });

            // Catalog name field change
            document.getElementById('catalogName').addEventListener('blur', function() {
                const catalogId = document.getElementById('catalogSelect').value;
                const newCatalogName = this.value.trim();
                
                if (catalogId && newCatalogName && !this.readOnly) {
                    // Clear all data and reset to initial state
        function clearAllData() {
            // Confirm action
            if (confirm('Are you sure you want to clear all data and reset the form? This action cannot be undone.')) {
                // Reset catalog selection
                document.getElementById('catalogSelect').selectedIndex = 0;
                
                // Reset catalog name field
                const catalogNameField = document.getElementById('catalogName');
                catalogNameField.value = '';
                catalogNameField.readOnly = true;
                catalogNameField.placeholder = 'Enter catalog name';
                catalogNameField.classList.remove('border-warning');
                
                // Reset lot selection
                const lotSelect = document.getElementById('lotSelect');
                lotSelect.innerHTML = '<option value="">Select Lot...</option>';
                lotSelect.disabled = true;
                
                // Reset header to initial state
                document.getElementById('catalogTitle').textContent = 'Select a catalog to begin';
                document.getElementById('displayCatalogNumber').textContent = 'Not selected';
                document.getElementById('displayLotNumber').textContent = 'Not selected';
                
                // Reset all sections to default state
                resetSectionsToDefault();
                
                // Disable all action buttons
                document.getElementById('loadDataBtn').disabled = true;
                document.getElementById('createNewBtn').disabled = true;
                document.getElementById('addLotBtn').disabled = true;
                document.getElementById('previewBtn').disabled = true;
                document.getElementById('generateBtn').disabled = true;
                
                // Clear sections data
                sectionsData = {};
                
                console.log('Data cleared successfully');
            }
        }

        // Update catalog name in database
                    updateCatalogName(catalogId, newCatalogName);
                }
            });

            // Lot selection
            document.getElementById('lotSelect').addEventListener('change', function() {
                // No need to update display here anymore since we show it only on Load Data
            });

            // Add catalog button
            document.getElementById('addCatalogBtn').addEventListener('click', function() {
                new bootstrap.Modal(document.getElementById('addCatalogModal')).show();
            });

            // Add lot button
            document.getElementById('addLotBtn').addEventListener('click', function() {
                const catalogSelect = document.getElementById('catalogSelect');
                const selectedOption = catalogSelect.options[catalogSelect.selectedIndex];
                const catalogName = document.getElementById('catalogName').value || 'Unnamed Product';
                const catalogNumber = selectedOption.getAttribute('data-catalog-number');
                document.getElementById('selectedCatalogInfo').value = `${catalogName} (${catalogNumber})`;
                new bootstrap.Modal(document.getElementById('addLotModal')).show();
            });

            // Save catalog button
            document.getElementById('saveCatalogBtn').addEventListener('click', saveNewCatalog);

            // Save lot button
            document.getElementById('saveLotBtn').addEventListener('click', saveNewLot);

            // Load data button
            document.getElementById('loadDataBtn').addEventListener('click', loadData);

            // Create new CoA button
            document.getElementById('createNewBtn').addEventListener('click', createNewCoA);

            // Clear data button
            document.getElementById('clearDataBtn').addEventListener('click', clearAllData);

            // Save section button (not needed for fixed sections)
            // document.getElementById('saveSectionBtn').addEventListener('click', saveNewSection);

            // Save key-value button
            document.getElementById('saveKeyValueBtn').addEventListener('click', function(e) {
                e.preventDefault();
                saveKeyValue();
            });

            // Key source change
            document.getElementById('keySource').addEventListener('change', function() {
                const source = this.value;
                loadExistingKeysForSection(source, currentSectionId);
            });

            // Generate PDF button
            document.getElementById('generateBtn').addEventListener('click', generatePDF);

            // Preview PDF button
            document.getElementById('previewBtn').addEventListener('click', previewPDF);
        }

        // Load catalogs from database
        function loadCatalogs() {
            fetch('api/get_catalog_data.php')
                .then(response => response.json())
                .then(data => {
                    const catalogSelect = document.getElementById('catalogSelect');
                    catalogSelect.innerHTML = '<option value="">Select Catalog...</option>';
                    
                    if (data.length === 0) {
                        // No catalogs exist, show helpful message
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
                        // No lots exist for this catalog
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
                    // Update the option data attribute
                    const catalogSelect = document.getElementById('catalogSelect');
                    const selectedOption = catalogSelect.options[catalogSelect.selectedIndex];
                    selectedOption.setAttribute('data-catalog-name', catalogName);
                    
                    // Make field readonly and remove warning border
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

        // Load templates (removed since not needed)
        function loadTemplates() {
            // Template functionality removed - using fixed sections
        }

        // Save new catalog
        function saveNewCatalog() {
            const catalogName = document.getElementById('newCatalogName').value.trim();
            const catalogNumber = document.getElementById('newCatalogNumber').value.trim();
            
            if (!catalogName) {
                alert('Please enter a catalog name');
                return;
            }
            
            if (!catalogNumber) {
                alert('Please enter a catalog number');
                return;
            }

            // Send to API
            fetch('api/save_catalog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    catalog_name: catalogName,
                    catalog_number: catalogNumber 
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload catalogs and select the new one
                    loadCatalogs();
                    
                    // Select the catalog after a brief delay to ensure options are loaded
                    setTimeout(() => {
                        document.getElementById('catalogSelect').value = data.catalog_id;
                        document.getElementById('catalogSelect').dispatchEvent(new Event('change'));
                    }, 100);
                    
                    // Clear form and close modal
                    document.getElementById('newCatalogName').value = '';
                    document.getElementById('newCatalogNumber').value = '';
                    bootstrap.Modal.getInstance(document.getElementById('addCatalogModal')).hide();
                    
                    // Show appropriate message based on action
                    if (data.action === 'created') {
                        alert('New catalog created successfully!');
                    } else if (data.action === 'updated') {
                        alert('Catalog name updated successfully!');
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

            // Send to API
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
                    // Reload lots and select the new one
                    loadLots(catalogId);
                    
                    // Select the new lot after a brief delay
                    setTimeout(() => {
                        document.getElementById('lotSelect').value = lotNumber;
                    }, 100);
                    
                    // Clear form and close modal
                    document.getElementById('newLotNumber').value = '';
                    bootstrap.Modal.getInstance(document.getElementById('addLotModal')).hide();
                    
                    // Show appropriate message based on action
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

        // Create new CoA (with blank template)
        function createNewCoA() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            
            if (!catalogId) {
                alert('Please select a catalog first');
                return;
            }
            
            if (!catalogName) {
                alert('Please enter a catalog name');
                return;
            }
            
            if (!lotNumber) {
                alert('Please select or create a lot first');
                return;
            }

            // Update header display
            const selectedOption = document.getElementById('catalogSelect').options[document.getElementById('catalogSelect').selectedIndex];
            const catalogNumber = selectedOption.getAttribute('data-catalog-number');
            
            document.getElementById('catalogTitle').textContent = catalogName;
            document.getElementById('displayCatalogNumber').textContent = catalogNumber;
            document.getElementById('displayLotNumber').textContent = lotNumber;

            // Clear all sections and show empty state
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = `
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-plus-circle me-2"></i>
                        Ready for new data - Click "Add Key-Value" to start
                    </div>
                `;
                
                // Enable add key-value buttons
                const addButton = container.parentElement.querySelector('.btn-outline-primary');
                if (addButton) {
                    addButton.disabled = false;
                }
            });

            // Initialize sections data
            sectionsData = {
                1: { name: 'Description', keyValues: [] },
                2: { name: 'Specifications', keyValues: [] },
                3: { name: 'Preparation and Storage', keyValues: [] }
            };

            // Enable action buttons
            document.getElementById('previewBtn').disabled = false;
            document.getElementById('generateBtn').disabled = false;
            
            alert('New CoA template ready! You can now add key-value pairs to each section.');
        }

        // Load data based on selections
        function loadData() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            
            if (!catalogId) {
                alert('Please select a catalog first');
                return;
            }
            
            if (!catalogName) {
                alert('Please enter a catalog name');
                return;
            }

            // Update header display with current values
            const selectedOption = document.getElementById('catalogSelect').options[document.getElementById('catalogSelect').selectedIndex];
            const catalogNumber = selectedOption.getAttribute('data-catalog-number');
            
            document.getElementById('catalogTitle').textContent = catalogName;
            document.getElementById('displayCatalogNumber').textContent = catalogNumber;
            document.getElementById('displayLotNumber').textContent = lotNumber || '-';

            // Show loading message in sections
            showLoadingInSections();

            // Reset sections to default empty state
        function resetSectionsToDefault() {
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = `
                    <div class="text-muted text-center py-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Select catalog and lot, then click "Load Data" to view content
                    </div>
                `;
                
                // Disable add key-value buttons
                const addButton = container.parentElement.querySelector('.btn-outline-primary');
                if (addButton) {
                    addButton.disabled = true;
                }
            });
            
            // Reset sections data
            sectionsData = {};
        }

        // Show loading state in sections
        function showLoadingInSections() {
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                const container = document.getElementById(`keyValues_${sectionId}`);
                container.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Loading data...
                    </div>
                `;
            });
        }

        // Load default sections and data
            loadDefaultSections(catalogId, lotNumber);
        }

        // Load default sections
        function loadDefaultSections(catalogId, lotNumber) {
            // Initialize sections data
            sectionsData = {
                1: { name: 'Description', keyValues: [] },
                2: { name: 'Specifications', keyValues: [] },
                3: { name: 'Preparation and Storage', keyValues: [] }
            };

            // Load data for each section
            const sections = [1, 2, 3];
            sections.forEach(sectionId => {
                loadSectionData(sectionId, catalogId, lotNumber);
            });

            // Enable action buttons
            document.getElementById('previewBtn').disabled = false;
            document.getElementById('generateBtn').disabled = false;
        }

        // Create section element
        function createSectionElement(sectionName, sectionId = null) {
            sectionCounter++;
            const elementId = sectionId || `section_${sectionCounter}`;
            
            const sectionHtml = `
                <div class="section-card" data-section-id="${elementId}">
                    <div class="section-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <h5 class="mb-0">${sectionName}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="section-content" id="section_${elementId}">
                        <div class="key-values-container" id="keyValues_${elementId}">
                            <!-- Key-value pairs will be loaded here -->
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-sm btn-outline-primary" onclick="addKeyValue('${elementId}')">
                                <i class="fas fa-plus"></i> Add Key-Value
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('sectionsContainer').insertAdjacentHTML('beforeend', sectionHtml);
            
            // Initialize sections data
            sectionsData[elementId] = { name: sectionName, keyValues: [] };
        }

        // Load section data from database
        function loadSectionData(sectionId, catalogId, lotNumber) {
            // Build API URL
            let apiUrl = `api/get_section_data.php?catalog_id=${catalogId}`;
            if (lotNumber) {
                apiUrl += `&lot_number=${lotNumber}`;
            }
            
            // Fetch data from API
            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error loading section data:', data.message);
                        return;
                    }
                    
                    // Handle both old format (direct array) and new format (with debug info)
                    const sectionsArray = Array.isArray(data) ? data : data.sections_data;
                    
                    if (data.debug_info) {
                        console.log('Debug info:', data.debug_info);
                    }
                    
                    // Find the section data for this sectionId
                    const sectionData = sectionsArray.find(section => section.section_id == sectionId);
                    
                    if (sectionData && sectionData.key_values.length > 0) {
                        // Clear existing content and add loaded data
                        const container = document.getElementById(`keyValues_${sectionId}`);
                        container.innerHTML = '';
                        
                        // Add each key-value pair
                        sectionData.key_values.forEach((kv, index) => {
                            const kvId = `kv_${sectionId}_${Date.now()}_${index}`;
                            addKeyValueToSectionFromData(sectionId, kv.key, kv.value, kv.source, kvId, kv.order);
                        });
                        
                        // Enable add key-value button for this section
                        const addButton = container.parentElement.querySelector('.btn-outline-primary');
                        if (addButton) {
                            addButton.disabled = false;
                        }
                    } else {
                        // No data found, show empty state but enable add button
                        const container = document.getElementById(`keyValues_${sectionId}`);
                        container.innerHTML = `
                            <div class="text-muted text-center py-3">
                                <i class="fas fa-database me-2"></i>
                                No data found for this section
                            </div>
                        `;
                        
                        // Enable add key-value button
                        const addButton = container.parentElement.querySelector('.btn-outline-primary');
                        if (addButton) {
                            addButton.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading section data:', error);
                });
        }

        // Add key-value to section from loaded data
        function addKeyValueToSectionFromData(sectionId, key, value, source, kvId, order) {
            const container = document.getElementById(`keyValues_${sectionId}`);
            
            const kvHtml = `
                <div class="key-value-row" id="${kvId}" data-order="${order}">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <i class="fas fa-grip-vertical drag-handle"></i>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">${key}</label>
                            <span class="badge bg-secondary source-badge ms-2">${source}</span>
                        </div>
                        <div class="col-md-7">
                            <textarea class="form-control" rows="2" onchange="updateKeyValue('${kvId}', this.value)">${value}</textarea>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-sm btn-outline-danger" onclick="removeKeyValue('${kvId}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', kvHtml);
            
            // Update data structure
            if (!sectionsData[sectionId]) {
                sectionsData[sectionId] = { name: '', keyValues: [] };
            }
            
            sectionsData[sectionId].keyValues.push({ 
                id: kvId, 
                key, 
                value, 
                source, 
                order: order || sectionsData[sectionId].keyValues.length + 1 
            });

            // Initialize sortable for this container if not already done
            if (!container.classList.contains('sortable-initialized')) {
                initializeSortableForSection(sectionId);
            }
        }

        // Add key-value pair
        function addKeyValue(sectionId) {
            currentSectionId = sectionId;
            
            // Load keys for the current section when modal opens
            loadExistingKeysForSection('catalog', sectionId);
            
            new bootstrap.Modal(document.getElementById('addKeyValueModal')).show();
        }

        // Save new section
        function saveNewSection() {
            const sectionName = document.getElementById('sectionName').value.trim();
            
            if (!sectionName) {
                alert('Please enter a section name');
                return;
            }

            createSectionElement(sectionName);
            
            // Clear form and close modal
            document.getElementById('sectionName').value = '';
            document.getElementById('sectionDescription').value = '';
            bootstrap.Modal.getInstance(document.getElementById('addSectionModal')).hide();
        }

        // Save key-value pair
        function saveKeyValue() {
            console.log('saveKeyValue function called'); // Debug log
            
            const source = document.getElementById('keySource').value;
            const existingKey = document.getElementById('existingKey').value;
            const customKey = document.getElementById('customKey').value.trim();
            const value = document.getElementById('keyValue').value.trim();

            console.log('Form values:', { source, existingKey, customKey, value }); // Debug log

            // Use existing key if selected, otherwise use custom key
            const key = existingKey || customKey;

            if (!key || !value) {
                alert('Please provide both key and value');
                return;
            }

            if (!currentSectionId) {
                alert('No section selected');
                return;
            }

            console.log('Adding key-value:', { key, value, source, sectionId: currentSectionId }); // Debug log

            // Remove focus from the button before closing modal (fixes ARIA warning)
            document.getElementById('saveKeyValueBtn').blur();
            
            // Add to section
            addKeyValueToSection(currentSectionId, key, value, source);

            // Clear form
            document.getElementById('customKey').value = '';
            document.getElementById('keyValue').value = '';
            document.getElementById('existingKey').selectedIndex = 0;
            
            // Close modal with proper focus management
            const modalElement = document.getElementById('addKeyValueModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                // Hide modal and then remove focus to prevent ARIA warning
                modal.hide();
                
                // After modal is hidden, ensure no focus issues
                modalElement.addEventListener('hidden.bs.modal', function(e) {
                    // Focus back to the add button that opened the modal
                    const addButton = document.querySelector(`[data-section-id="${currentSectionId}"] .btn-outline-primary`);
                    if (addButton) {
                        addButton.focus();
                    }
                }, { once: true });
            }
            
            console.log('Key-value pair added successfully'); // Debug log
        }

        // Add key-value to section
        function addKeyValueToSection(sectionId, key, value, source) {
            console.log('addKeyValueToSection called with:', { sectionId, key, value, source }); // Debug log
            
            const container = document.getElementById(`keyValues_${sectionId}`);
            if (!container) {
                console.error('Container not found for section:', sectionId);
                alert('Error: Section container not found');
                return;
            }
            
            const kvId = `kv_${Date.now()}`;
            
            const kvHtml = `
                <div class="key-value-row" id="${kvId}" data-order="${sectionsData[sectionId] ? sectionsData[sectionId].keyValues.length + 1 : 1}">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <i class="fas fa-grip-vertical drag-handle"></i>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">${key}</label>
                            <span class="badge bg-secondary source-badge ms-2">${source}</span>
                        </div>
                        <div class="col-md-7">
                            <textarea class="form-control" rows="2" onchange="updateKeyValue('${kvId}', this.value)">${value}</textarea>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-sm btn-outline-danger" onclick="removeKeyValue('${kvId}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', kvHtml);
            
            // Initialize or update data structure
            if (!sectionsData[sectionId]) {
                sectionsData[sectionId] = { name: '', keyValues: [] };
            }
            
            // Update data structure
            sectionsData[sectionId].keyValues.push({ 
                id: kvId, 
                key, 
                value, 
                source, 
                order: sectionsData[sectionId].keyValues.length + 1 
            });

            // Initialize sortable for this container if not already done
            if (!container.classList.contains('sortable-initialized')) {
                initializeSortableForSection(sectionId);
            }
            
            console.log('Key-value pair added to DOM and data structure'); // Debug log
            
            // Save to database
            saveKeyValueToDatabase(sectionId, key, value, source);
        }

        // Save key-value to database
        function saveKeyValueToDatabase(sectionId, key, value, source) {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            
            if (!catalogId) {
                console.error('No catalog selected');
                return;
            }
            
            const payload = {
                catalog_id: catalogId,
                section_id: sectionId,
                key: key,
                value: value,
                source: source
            };
            
            // Add lot_number if it's lot data
            if (source === 'lot' && lotNumber) {
                payload.lot_number = lotNumber;
            }
            
            console.log('Saving to database:', payload); // Debug log
            
            fetch('api/save_key_value.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Key-value saved to database successfully:', data);
                } else {
                    console.error('Error saving to database:', data.message);
                    alert('Error saving to database: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Network error saving key-value:', error);
                alert('Network error saving to database. Please try again.');
            });
        }

        // Initialize sortable functionality for a section
        function initializeSortableForSection(sectionId) {
            const container = document.getElementById(`keyValues_${sectionId}`);
            
            new Sortable(container, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'sortable-ghost',
                onEnd: function(evt) {
                    updateKeyValueOrderInDatabase(sectionId);
                }
            });
            container.classList.add('sortable-initialized');
        }

        // Update key-value order after drag and drop
        function updateKeyValueOrderInDatabase(sectionId) {
            const container = document.getElementById(`keyValues_${sectionId}`);
            const keyValueElements = container.querySelectorAll('.key-value-row');
            
            // Build array of new orders
            const keyValueOrders = [];
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            
            keyValueElements.forEach((element, index) => {
                const newOrder = index + 1;
                const kvId = element.id;
                
                // Update the visual order attribute
                element.setAttribute('data-order', newOrder);
                
                // Find the key-value data
                const keyValue = sectionsData[sectionId].keyValues.find(kv => kv.id === kvId);
                if (keyValue) {
                    keyValue.order = newOrder;
                    
                    // Add to update array
                    keyValueOrders.push({
                        key: keyValue.key,
                        source: keyValue.source,
                        order: newOrder
                    });
                }
            });
            
            // Save to database immediately
            if (keyValueOrders.length > 0 && catalogId) {
                saveKeyValueOrderToDatabase(catalogId, sectionId, lotNumber, keyValueOrders);
            }
        }

        // Save key-value order to database
        function saveKeyValueOrderToDatabase(catalogId, sectionId, lotNumber, keyValueOrders) {
            const payload = {
                catalog_id: catalogId,
                section_id: sectionId,
                lot_number: lotNumber || '',
                key_value_orders: keyValueOrders
            };
            
            fetch('api/save_key_value_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log(`Order updated for section ${sectionId}: ${data.updated_count} items`);
                    
                    // Show subtle feedback
                    showOrderUpdateFeedback(sectionId);
                } else {
                    console.error('Error updating order:', data.message);
                    alert('Error saving new order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error saving order:', error);
                alert('Error saving new order. Please try again.');
            });
        }

        // Show visual feedback that order was saved
        function showOrderUpdateFeedback(sectionId) {
            const sectionHeader = document.querySelector(`[data-section-id="${sectionId}"] .section-header h5`);
            if (sectionHeader) {
                // Temporarily add a success indicator
                const originalText = sectionHeader.textContent;
                sectionHeader.innerHTML = `${originalText} <i class="fas fa-check text-success ms-2"></i>`;
                
                // Remove after 2 seconds
                setTimeout(() => {
                    sectionHeader.textContent = originalText;
                }, 2000);
            }
        }

        // Update key value when textarea changes
        function updateKeyValue(kvId, newValue) {
            // Find and update the key-value in data structure
            Object.keys(sectionsData).forEach(sectionId => {
                const keyValue = sectionsData[sectionId].keyValues.find(kv => kv.id === kvId);
                if (keyValue) {
                    keyValue.value = newValue;
                }
            });
        }

        // Remove section
        function removeSection(sectionId) {
            // Sections are now fixed, so this function is not needed
            // Keeping for backward compatibility but it won't be called
        }

        // Remove key-value pair
        function removeKeyValue(kvId) {
            console.log('removeKeyValue called for:', kvId); // Debug log
            
            if (!confirm('Are you sure you want to remove this key-value pair?')) {
                return;
            }
            
            // Find the key-value data before removing from DOM
            let keyValueData = null;
            let sectionId = null;
            
            // Search through all sections to find the key-value pair
            Object.keys(sectionsData).forEach(secId => {
                const keyValue = sectionsData[secId].keyValues.find(kv => kv.id === kvId);
                if (keyValue) {
                    keyValueData = keyValue;
                    sectionId = secId;
                }
            });
            
            if (!keyValueData) {
                console.error('Key-value data not found for ID:', kvId);
                alert('Error: Could not find key-value data to delete');
                return;
            }
            
            console.log('Found key-value to delete:', keyValueData); // Debug log
            
            // Remove from DOM
            const element = document.getElementById(kvId);
            if (element) {
                element.remove();
            }
            
            // Update data structure
            if (sectionsData[sectionId]) {
                sectionsData[sectionId].keyValues = sectionsData[sectionId].keyValues.filter(kv => kv.id !== kvId);
            }
            
            // Delete from database
            deleteKeyValueFromDatabase(sectionId, keyValueData.key, keyValueData.source);
            
            console.log('Key-value pair removed from DOM and data structure'); // Debug log
        }

        // Delete key-value from database
        function deleteKeyValueFromDatabase(sectionId, key, source) {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            
            if (!catalogId) {
                console.error('No catalog selected');
                return;
            }
            
            const payload = {
                catalog_id: catalogId,
                section_id: sectionId,
                key: key,
                source: source
            };
            
            // Add lot_number if it's lot data
            if (source === 'lot' && lotNumber) {
                payload.lot_number = lotNumber;
            }
            
            console.log('Deleting from database:', payload); // Debug log
            
            fetch('api/delete_key_value.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Key-value deleted from database successfully:', data);
                } else {
                    console.error('Error deleting from database:', data.message);
                    alert('Error deleting from database: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Network error deleting key-value:', error);
                alert('Network error deleting from database. Please try again.');
            });
        }

        // Load existing keys for specific section
        function loadExistingKeysForSection(source, sectionId) {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            
            if (!catalogId || !sectionId) {
                return;
            }

            // Build API URL
            let apiUrl = `api/get_existing_keys.php?catalog_id=${catalogId}&section_id=${sectionId}&source=${source}`;
            if (lotNumber && source === 'lot') {
                apiUrl += `&lot_number=${lotNumber}`;
            }

            fetch(apiUrl)
                .then(response => response.json())
                .then(data => {
                    const existingKey = document.getElementById('existingKey');
                    existingKey.innerHTML = '<option value="">Select existing key...</option>';
                    
                    if (data.success && data.keys && data.keys.length > 0) {
                        data.keys.forEach(keyName => {
                            const option = document.createElement('option');
                            option.value = keyName;
                            option.textContent = keyName;
                            existingKey.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading existing keys:', error);
                    const existingKey = document.getElementById('existingKey');
                    existingKey.innerHTML = '<option value="">Error loading keys</option>';
                });
        }

        // Generate PDF
        function generatePDF() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            
            // Validation
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

            console.log('Generating PDF for:', { catalogId, lotNumber, catalogName });

            // Create URL for PDF generation
            const generateUrl = `api/generate_pdf.php?catalog_id=${catalogId}&lot_number=${encodeURIComponent(lotNumber)}`;
            
            // Open in new tab for download
            window.open(generateUrl, '_blank');
        }

        // Preview PDF
        function previewPDF() {
            const catalogId = document.getElementById('catalogSelect').value;
            const lotNumber = document.getElementById('lotSelect').value;
            const catalogName = document.getElementById('catalogName').value.trim();
            
            // Validation
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

            console.log('Previewing PDF for:', { catalogId, lotNumber, catalogName });

            // Create URL for PDF preview
            const previewUrl = `api/preview_pdf.php?catalog_id=${catalogId}&lot_number=${encodeURIComponent(lotNumber)}`;
            
            // Open in new tab for preview
            window.open(previewUrl, '_blank');
        }

        // Load templates
        function loadTemplates() {
            fetch('api/get_templates.php')
                .then(response => response.json())
                .then(data => {
                    const templateSelect = document.getElementById('templateSelect');
                    templateSelect.innerHTML = '<option value="">Select Template...</option>';
                    
                    if (data.success && data.templates) {
                        data.templates.forEach(template => {
                            const option = document.createElement('option');
                            option.value = template.id;
                            option.textContent = template.template_name;
                            if (template.is_default) {
                                option.textContent += ' (Default)';
                            }
                            templateSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading templates:', error);
                });
        }

        // Load catalog's current template
        function loadCatalogTemplate(catalogId) {
            // This function will check if the catalog has a template assigned
            // and select it in the dropdown
            fetch(`api/get_catalog_template.php?catalog_id=${catalogId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.template_id) {
                        document.getElementById('templateSelect').value = data.template_id;
                    }
                })
                .catch(error => {
                    console.error('Error loading catalog template:', error);
                });
        }

        // Preview PDF
        function previewPDF() {
            // Similar to generatePDF but opens in new tab
            alert('Preview functionality will be implemented next');
        }
    </script>
</body>
</html>