<?php
// No Data Available Component
// This component displays a beautiful "No Data Available" message when there's no data to show
?>

<div class="no-data-container">
    <div class="no-data-content">
        <div class="no-data-icon">
            <i class="bi bi-inbox"></i>
        </div>
        <h4 class="no-data-title">No Data Available</h4>
        <p class="no-data-message">There are currently no records to display.</p>
        <div class="no-data-actions">
            <button type="button" class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
</div>

<style>
.no-data-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 400px;
    padding: 2rem;
}

.no-data-content {
    text-align: center;
    max-width: 400px;
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
}

.no-data-icon {
    font-size: 4rem;
    color: #6c757d;
    margin-bottom: 1rem;
}

.no-data-title {
    color: #495057;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 1.5rem;
}

.no-data-message {
    color: #6c757d;
    margin-bottom: 1.5rem;
    font-size: 1rem;
    line-height: 1.5;
}

.no-data-actions .btn {
    border-radius: 8px;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.no-data-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .no-data-container {
        min-height: 300px;
        padding: 1rem;
    }
    
    .no-data-content {
        padding: 1.5rem;
        max-width: 100%;
    }
    
    .no-data-icon {
        font-size: 3rem;
    }
    
    .no-data-title {
        font-size: 1.25rem;
    }
    
    .no-data-message {
        font-size: 0.9rem;
    }
}
</style>
