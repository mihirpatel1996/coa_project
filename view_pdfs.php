<?php 
$pageTitle = "Generated PDFs";
include './includes/header.php';
?>

<style>
    /* Input labels for this page */
    .input-group-text{
        /* //make input group text look like normal labels */
        background-color: white;
        border: none;
        font-size: inherit;
        font-weight: normal;
    }
</style>

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
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text">From Date</span>
                                                <input type="date" class="form-control" id="fromDate">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text">To</span>
                                                <input type="date" class="form-control" id="toDate">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <span class="input-group-text">Catalog/Lot #</span>
                                                <input type="text" class="form-control" id="searchQuery" placeholder="Enter Catalog or Lot Number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-center">
                                    <button type="submit" class="btn btn-primary w-90">
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
                        <div class="table-responsive" style="display: none; overflow-x: hidden;">
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
                //if from date is inserted but to date is missing
                if((fromDate && !toDate) || (!fromDate && toDate)) {
                    alert("Please provide both From Date and To Date for date range search.");
                    return;
                }

                if(fromDate && toDate) {
                    if (new Date(fromDate) > new Date(toDate)) {
                        alert("From Date cannot be later than To Date.");
                        return;
                    }
                }

                //if search query is inserted, it shouldn't be longer than 255 characters
                if(searchQuery && searchQuery.length > 255) {
                    alert("Search query cannot be longer than 255 characters.");
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
                        displayPdfs(data.pdfs, data.missing);
                    } else {
                        alert('Error fetching PDFs: ' + (data.message || 'Unknown error'));
                        displayPdfs([], []); // Clear results on error
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching PDFs.');
                    displayPdfs([], []); // Clear results on network error
                });
            });

            function displayPdfs(pdfs, missing) {
                pdfTableBody.innerHTML = ''; // Clear previous results
                console.log("missing: "+missing);
                let missingHtml = '';
                if (missing && missing.count > 0) {
                    console.log("missing terms:"+missing.terms);
                    const missingTermsStr = missing.terms.join(', ');
                    missingHtml = `
                        <div class="alert alert-warning" role="alert">
                            <p class="mb-0"><strong>${missing.count}</strong> items from your search were not found: <strong>${missingTermsStr}</strong></p>
                        </div>
                    `;
                }

                if (pdfs.length > 0) {
                    pdfResultsDiv.style.display = 'none';
                    
                    // Add Download All button
                    const downloadAllHtml = `
                        <div class="mb-3">
                            <button id="downloadAllBtn" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Download All PDFs (${pdfs.length} files)
                            </button>
                        </div>
                    `;
                    
                    // Group PDFs by date
                    const groupedPdfs = {};
                    pdfs.forEach(pdf => {
                        const date = new Date(pdf.generatedAt).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                        if (!groupedPdfs[date]) {
                            groupedPdfs[date] = [];
                        }
                        groupedPdfs[date].push(pdf);
                    });
                    
                    // Build HTML for grouped display
                    let html = missingHtml + downloadAllHtml + '<div class="pdf-groups">';
                    
                    // Sort dates in descending order (newest first)
                    const sortedDates = Object.keys(groupedPdfs).sort((a, b) => 
                        new Date(b) - new Date(a)
                    );
                    
                    sortedDates.forEach(date => {
                        html += `
                            <div class="date-group mb-4">
                                <h6 class="date-header">
                                    <i class="fas fa-calendar-alt me-2"></i>${date}
                                </h6>
                                <div class="pdf-list">
                                    <div class="pdf-item">
                        `;
                        
                        groupedPdfs[date].forEach(pdf => {
                            const time = new Date(pdf.generatedAt).toLocaleTimeString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: true
                            });
                            
                            html += `

                                    <i class="fas fa-file-pdf text-danger me-1"></i>
                                    <a href="./generated_pdfs/${pdf.fileName}" target="_blank" class="pdf-link me-3">
                                    <span class="pdf-name" style="font-size: 0.9rem;">${pdf.fileName}</span>
                                    </a>
                                    <!-- <span class="pdf-time text-muted ms-3 me-2">${time}</span> -->
                               
                            `;
                        });
                        
                        html += `
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    
                    // Replace table with new grouped display
                    document.querySelector('.table-responsive').innerHTML = html;
                    document.querySelector('.table-responsive').style.display = 'block';
                    
                    // Add event listener for Download All button
                    document.getElementById('downloadAllBtn').addEventListener('click', function() {
                        downloadAllPdfs(pdfs);
                    });
                    
                } else {
                    pdfResultsDiv.style.display = 'block';
                    let noResultsHtml = `
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No PDF files found matching your criteria.
                    `;
                    pdfResultsDiv.innerHTML = missingHtml + noResultsHtml;
                    tableResponsiveDiv.style.display = 'none';
                }
            }

            function downloadAllPdfs(pdfs) {
                const btn = document.getElementById('downloadAllBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Preparing download...';
                btn.disabled = true;
                
                // Prepare list of filenames
                const filenames = pdfs.map(pdf => pdf.fileName);
                
                // Call bulk download API
                fetch('api/download_all_pdfs.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ filenames: filenames })
                })
                .then(response => response.blob())
                .then(blob => {
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `pdfs_${new Date().toISOString().split('T')[0]}.zip`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                })
                .catch(error => {
                    console.error('Error downloading PDFs:', error);
                    alert('Error downloading PDFs. Please try again.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            }
        });
    </script>
</body>