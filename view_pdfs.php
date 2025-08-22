<?php 
$pageTitle = "Generated PDFs";
include './includes/header.php';
?>

<body>
    <!-- Include navbar -->
    <?php include './includes/navbar.php'?>

    <div class="container mt-2">  
        <div class="row mb-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Search Generated PDFs</h5>
                        <form id="pdfSearchForm">
                            <p class="text-muted mb-3"><i class="fas fa fa-info-circle me-2"></i> Search by **Date Range** OR by **Catalog/Lot Number**.</p>
                            <div class="row g-3 align-items-end">
                                <div class="col-md-3">
                                    <label for="fromDate" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="fromDate">
                                </div>
                                <div class="col-md-3">
                                    <label for="toDate" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="toDate">
                                </div>
                                <div class="col-md-1 text-center">
                                    <p><strong>OR</strong></p>
                                </div>
                                <div class="col-md-3">
                                    <label for="searchQuery" class="form-label">Catalog/Lot Number</label>
                                    <input type="text" class="form-control" id="searchQuery" placeholder="Enter Catalog or Lot Number">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Placeholder for PDF results - will be populated by JavaScript later -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Generated PDFs</h5>
                        <div id="pdfResults" class="text-muted text-center py-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Search to display PDF files here.
                        </div>
                        <div class="table-responsive" style="display: none;">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Date Generated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pdfTableBody">
                                    <!-- PDF rows will be inserted here by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pdfSearchForm = document.getElementById('pdfSearchForm');
            const fromDateInput = document.getElementById('fromDate');
            const toDateInput = document.getElementById('toDate');
            const searchQueryInput = document.getElementById('searchQuery');
            const pdfResultsDiv = document.getElementById('pdfResults');
            const pdfTableBody = document.getElementById('pdfTableBody');
            const tableResponsiveDiv = document.querySelector('.table-responsive');

            // Set to today's date by default
            fromDateInput.valueAsDate = new Date();
            toDateInput.valueAsDate = new Date();
            
            // Clear fromDate and toDate inputs when search query is entered
            searchQueryInput.addEventListener('keyup', function(event){
                if(fromDateInput.valueAsDate != null || toDateInput.valueAsDate != null) {
                    fromDateInput.value = ""; // Clear value
                    toDateInput.value = ""; // Clear value
                }
            });

            // fromDate or toDate is changed, clear search query input
            fromDateInput.addEventListener('change', function(event){
                searchQueryInput.value = "";
            });
            toDateInput.addEventListener('change', function(event){
                searchQueryInput.value = "";
            });

            pdfSearchForm.addEventListener('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                const fromDate = fromDateInput.value;
                const toDate = toDateInput.value;
                const searchQuery = searchQueryInput.value.toLowerCase().trim();
                
                // Validate inputs
                if ((!fromDate && !toDate && !searchQuery)) {
                    alert("Please select a date range or enter a search query.");
                    return;
                }

                // Prepare data for API call
                const postData = {
                    searchType: searchQuery ? 'query' : 'date',
                    fromDate: fromDate,
                    toDate: toDate,
                    searchQuery: searchQuery
                };

                // Make API call
                fetch('api/get_pdfs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayPdfs(data.pdfs);
                    } else {
                        alert('Error fetching PDFs: ' + (data.message || 'Unknown error'));
                        displayPdfs([]); // Clear results on error
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching PDFs.');
                    displayPdfs([]); // Clear results on network error
                });
            });

            function displayPdfs(pdfs) {
                pdfTableBody.innerHTML = ''; // Clear previous results

                if (pdfs.length > 0) {
                    pdfResultsDiv.style.display = 'none'; // Hide "Search to display..." message
                    tableResponsiveDiv.style.display = 'block'; // Show the table

                    pdfs.forEach(pdf => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${pdf.fileName}</td>
                            <td>${pdf.generatedAt}</td>
                            <td><a href="api/generated_pdfs/${pdf.fileName}" class="btn btn-sm btn-success" download><i class="fas fa-download"></i> Download</a></td>
                        `;
                        pdfTableBody.appendChild(row);
                    });
                } else {
                    pdfResultsDiv.style.display = 'block'; // Show message
                    pdfResultsDiv.innerHTML = `
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No PDF files found matching your criteria.
                    `;
                    tableResponsiveDiv.style.display = 'none'; // Hide the table
                }
            }
        });
    </script>
</body>