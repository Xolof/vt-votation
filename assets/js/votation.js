const buttonNodeList = document.querySelectorAll(".votationButton");
const checkBoxInputs = document.querySelectorAll("input[type=checkbox]");

let checkforSubmission = setInterval(() => {
  if (document.querySelector(".forminator-success")) {
    Array.from(buttonNodeList).forEach(element => {
      element.style.display = "none";
      element.style.backgroundColor = "#4880bf"; 
      element.style.color = "#ddd"; 
      element.setAttribute("disabled", true);
    });
  };
}, 10);


const checkBoxImageURL = "/app/plugins/vt-votation/assets/images/checkmark-svgrepo-com.svg";

function toggleAdded(target) {
  const addText = "Lägg till i min lista";
  const addedText = `Tillagd i din lista &nbsp; <img src="${checkBoxImageURL}" alt="checkbox" />`;
  if (target.innerHTML !== addText) {
    target.innerHTML = addText;
  } else {
    target.innerHTML = addedText;
  }
}

[...buttonNodeList].forEach(element => {
  element.addEventListener("click", (e) => {
    if (e.target.getAttribute("disabled")) {
      return;
    };
      
    toggleAdded(e.target);

    const filteredClasses = Array.from(e.target.classList)
      .filter(cssClass => {
        checkBoxInputs.forEach(input => {
          if (input.value === cssClass) {
            input.checked = !input.checked;
          }
        });
      });
  });
});


[...checkBoxInputs].forEach(element => {
  element.addEventListener("click", (e) => {
    filteredButtons = Array.from(buttonNodeList).filter(button => {
      return button.classList.contains(e.target.value)
    });
    toggleAdded(filteredButtons[0]);
  });
});

