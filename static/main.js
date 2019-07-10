
let isRightSidePanelExpanded = false;
let isLeftSidePanelExpanded = false;

function toggleSidePanel(panel) {
    console.log("clicked " + panel);

    if (!['left', 'right'].includes(panel)) throw new Error("'panel' must be left or right!");

    const leftSidePanel = document.getElementById('left-panel');
    const rightSidePanel = document.getElementById('right-panel');

    if (panel === 'left') isLeftSidePanelExpanded = !isLeftSidePanelExpanded;
    if (panel === 'right') isRightSidePanelExpanded = !isRightSidePanelExpanded;

    leftSidePanel.style = !!isLeftSidePanelExpanded ? "flex: 5;" : "flex: 0;";
    rightSidePanel.style = !!isRightSidePanelExpanded ? "flex: 10;" : "flex: 0;";
}

function closeRightSidePanel() {
    isRightSidePanelExpanded = true;
    toggleSidePanel('right');
}

function loadImage(file) {
    console.log("Load Image");
    const image = document.getElementById('loaded-image');
    image.src = '/stockfile' + file;
    isRightSidePanelExpanded = false;
    toggleSidePanel('right');
}