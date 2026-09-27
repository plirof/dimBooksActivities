var groupsort = {
    groups: [],
    items: [],
    placedItems: [],
    
    init: function(data) {
        this.groups = data.groups;
        this.items = [];
        this.placedItems = [];
        
        var allItems = [];
        for (var i = 0; i < this.groups.length; i++) {
            for (var j = 0; j < this.groups[i].items.length; j++) {
                allItems.push({
                    text: this.groups[i].items[j],
                    groupId: i
                });
            }
        }
        
        this.shuffleArray(allItems);
        this.items = allItems;
        
        this.renderItems();
        this.renderGroups();
        
        document.getElementById('total').textContent = allItems.length;
    },
    
    shuffleArray: function(array) {
        for (var i = array.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var temp = array[i];
            array[i] = array[j];
            array[j] = temp;
        }
    },
    
    renderItems: function() {
        var itemsArea = document.getElementById('itemsArea');
        itemsArea.innerHTML = '';
        
        for (var i = 0; i < this.items.length; i++) {
            if (!this.isItemPlaced(i)) {
                var itemDiv = document.createElement('div');
                itemDiv.className = 'item';
                itemDiv.textContent = this.items[i].text;
                itemDiv.draggable = true;
                itemDiv.dataset.index = i;
                
                itemDiv.addEventListener('dragstart', this.onItemDragStart.bind(this));
                itemDiv.addEventListener('click', this.onItemClick.bind(this, i));
                
                itemsArea.appendChild(itemDiv);
            }
        }
    },
    
    renderGroups: function() {
        var groupsArea = document.getElementById('groupsArea');
        groupsArea.innerHTML = '';
        
        for (var i = 0; i < this.groups.length; i++) {
            var groupDiv = document.createElement('div');
            groupDiv.className = 'group-box';
            groupDiv.dataset.groupId = i;
            
            groupDiv.innerHTML = '<h4>' + this.groups[i].name + '</h4>';
            
            groupDiv.addEventListener('dragover', this.onGroupDragOver.bind(this));
            groupDiv.addEventListener('drop', this.onGroupDrop.bind(this));
            
            groupsArea.appendChild(groupDiv);
        }
    },
    
    onItemClick: function(itemIndex) {
        var item = this.items[itemIndex];
        
        var groupId = prompt('Which group? Enter group number (1-' + this.groups.length + ')');
        if (groupId !== null) {
            groupId = parseInt(groupId) - 1;
            if (groupId >= 0 && groupId < this.groups.length) {
                this.placeItem(itemIndex, groupId);
            } else {
                alert('Invalid group number.');
            }
        }
    },
    
    onItemDragStart: function(e) {
        e.dataTransfer.setData('text/plain', e.target.dataset.index);
    },
    
    onGroupDragOver: function(e) {
        e.preventDefault();
    },
    
    onGroupDrop: function(e) {
        e.preventDefault();
        var itemIndex = parseInt(e.dataTransfer.getData('text/plain'));
        var groupId = parseInt(e.currentTarget.dataset.groupId);
        
        this.placeItem(itemIndex, groupId);
    },
    
    isItemPlaced: function(itemIndex) {
        for (var i = 0; i < this.placedItems.length; i++) {
            if (this.placedItems[i].itemIndex === itemIndex) {
                return true;
            }
        }
        return false;
    },
    
    placeItem: function(itemIndex, groupId) {
        if (this.isItemPlaced(itemIndex)) {
            return;
        }
        
        this.placedItems.push({
            itemIndex: itemIndex,
            groupId: groupId
        });
        
        var groupBox = document.querySelector('.group-box[data-group-id="' + groupId + '"]');
        var itemDiv = document.createElement('div');
        itemDiv.className = 'item';
        itemDiv.textContent = this.items[itemIndex].text;
        itemDiv.dataset.itemIndex = itemIndex;
        itemDiv.dataset.groupId = groupId;
        
        var resetBtn = document.createElement('button');
        resetBtn.type = 'button';
        resetBtn.textContent = '×';
        resetBtn.style.marginLeft = '5px';
        resetBtn.style.padding = '2px 8px';
        resetBtn.style.background = '#dc3545';
        resetBtn.style.color = 'white';
        resetBtn.style.border = 'none';
        resetBtn.style.borderRadius = '3px';
        resetBtn.style.cursor = 'pointer';
        resetBtn.onclick = this.resetItem.bind(this, itemIndex);
        
        itemDiv.appendChild(resetBtn);
        groupBox.appendChild(itemDiv);
        
        this.renderItems();
        document.getElementById('score').textContent = this.placedItems.length;
        
        if (this.placedItems.length === this.items.length) {
            this.allItemsPlaced();
        }
    },
    
    resetItem: function(itemIndex) {
        for (var i = 0; i < this.placedItems.length; i++) {
            if (this.placedItems[i].itemIndex === itemIndex) {
                var itemElement = document.querySelector('.item[data-item-index="' + itemIndex + '"]');
                if (itemElement) {
                    itemElement.parentNode.removeChild(itemElement);
                }
                this.placedItems.splice(i, 1);
                break;
            }
        }
        
        this.renderItems();
        document.getElementById('score').textContent = this.placedItems.length;
    },
    
    allItemsPlaced: function() {
        alert('All items placed! Click "Check Answers" to see if they are correct.');
    },
    
    checkAnswers: function() {
        if (this.placedItems.length === 0) {
            alert('Please place items into groups first.');
            return;
        }
        
        var correctCount = 0;
        
        for (var i = 0; i < this.placedItems.length; i++) {
            var placed = this.placedItems[i];
            var item = this.items[placed.itemIndex];
            var itemElement = document.querySelector('.item[data-item-index="' + placed.itemIndex + '"]');
            
            itemElement.classList.remove('placed', 'wrong');
            
            if (item.groupId === placed.groupId) {
                itemElement.classList.add('placed');
                correctCount++;
            } else {
                itemElement.classList.add('wrong');
            }
        }
        
        var percentage = Math.round((correctCount / this.placedItems.length) * 100);
        
        if (percentage === 100) {
            alert('Perfect! All items are in the correct groups!');
        } else if (percentage >= 80) {
            alert('Great job! ' + percentage + '% correct!');
        } else {
            alert('Keep trying! ' + percentage + '% correct.');
        }
        
        this.saveResult(percentage);
    },
    
    saveResult: function(percentage) {
        var urlParams = new URLSearchParams(window.location.search);
        var activityId = urlParams.get('id');
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../api/save_result.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (!response.success) {
                    console.error('Failed to save result:', response.message);
                }
            }
        };
        
        xhr.send(JSON.stringify({
            activity_id: activityId,
            score: percentage
        }));
    }
};

window.onload = function() {
    var urlParams = new URLSearchParams(window.location.search);
    var activityId = urlParams.get('id');
    
    if (!activityId) {
        alert('Activity ID is required.');
        window.location.href = '../dashboard.php';
        return;
    }
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../api/load_activity.php', true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success && response.activity && response.activity.type === 'groupsort') {
                document.getElementById('groupsortTitle').textContent = response.activity.title;
                groupsort.init(response.activity.data);
            } else {
                alert('Failed to load activity.');
                window.location.href = '../dashboard.php';
            }
        }
    };
    
    xhr.send(JSON.stringify({
        id: activityId
    }));
};
