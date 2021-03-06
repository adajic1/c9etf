define(function(require, exports, module) {
    "use strict";
    
    var oop = require("ace/lib/oop");
    var Base = require("ace_tree/list_data");
    
    var ListData = function(commands, tabManager) {
        Base.call(this);
        
        this.classes = {};
        
        // todo compute these automatically
        this.innerRowHeight = 34;
        this.rowHeight = 42;
        
        this.commands = commands;
        this.tabManager = tabManager;
        this.updateData();
        this.myitems = [];
        
        Object.defineProperty(this, "loaded", {
            get: function(){ return this.visibleItems.length; }
        });
    };
    oop.inherits(ListData, Base);
    (function() {
        
        var cache;
        
        this.addORItem = function(item) {
            this.visibleItems.push(item);
        }
        
        
        this.updateData = function(subset) {
            this.visibleItems = subset || this.visibleItems;
            
            // @TODO Deal with selection
            this._signal("change");
        };
        
        this.getEmptyMessage = function(){
            if (!this.keyword)
                return "Učitavam nalaze testa,<br> molimo sačekajte par sekundi...";
            else
                return this.keyword;
        };
        
        this.replaceStrong = function(value) {
            if (!value)
                return "";
                
            var keyword = (this.keyword || "").replace(/\*/g, "");
            var i;
            if ((i = value.lastIndexOf(keyword)) !== -1)
                return value.substring(0, i) + "<strong>" + keyword + "</strong>" 
                    + value.substring(i+keyword.length);
            
            var result = this.search.matchPath(value, keyword);
            if (!result.length)
                return value;
                
            result.forEach(function(part, i) {
                if (part.match)
                    result[i] = "<strong>" + part.val + "</strong>";
                else
                    result[i] = part.val;
            });
            return result.join("");
        };
    
        this.renderRow = function(row, html, config) {
            var key = this.visibleItems[row];
            var isSelected = this.isSelected(row);
            
            // disabled available check since it breaks most editor commands
            var available = true;
            var keys = '';
	    
	    var subclass = '';
	    // Absolute paths neccessary :(
	    keys = '<img src="/static/plugins/etf.buildservice/images/nevalja.png">';
		    if (key.desc == "OK")
			    keys = '<img src="/static/plugins/etf.buildservice/images/ok.png">';
		    else if (key.label.trim() == "")
			    keys = "";
            
            html.push("<div class='item " + subclass + (available && isSelected ? "selected " : "") 
                + (available && this.getClassName(row))
                + (available ? "" : " notAvailable")
                + "' style='height:" + this.innerRowHeight + "px'>"
                + "<span class='keys'>" + keys + "</span>"
                + "<span class='caption'>"
                + key.label
                + "</span><span class='path'>"
                    + key.desc
                + "</span></div>");
        };
        
        this.getText = function(node) {
            var command = this.commands.commands[node.id];
            if (!command) return "";
            return (command.group || "General") + ": "
                + (command.displayName || command.name || node.id)
                + (command.hint ? "\n" + command.hint : "");
        };
        
        this.getClassName = function(row) {
            return this.classes[row] || "";
        };
        
        this.setClass = function(node, className, include) {
            if (include)
                this.classes[node.index] = className;
            else
                delete this.classes[node.index];
            this._signal("changeClass");
        };
        
    }).call(ListData.prototype);
    
    return ListData;
});
