package area

const STATUS_ACTIVE = 3
const STATUS_INACTIVE = 0

type Area struct {
	number      int
	name        string
	description string
	status      byte
}

func NewArea(number int, name string, description string, status byte) *Area {
	return &Area{number, name, description, status}
}

func (area *Area) GetDescription() string {
	return area.description
}

func (area *Area) GetName() string {
	return area.name
}

func (area *Area) GetStatus() byte {
	return area.status
}
